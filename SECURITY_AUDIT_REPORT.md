# Poster Printer Security Audit Report

**Date:** 2026-03-06
**Auditor:** Automated Code Analysis
**Branch:** heel-one
**Version:** 2.0 Beta

---

## Executive Summary

This audit identified **19 distinct vulnerabilities** across the poster printer application, including **3 critical**, **5 high**, **7 medium**, and **4 low-severity** issues. The most severe findings are unauthenticated arbitrary file reads via path traversal, a command injection vector in file processing, and SQL injection in the order management layer.

---

## Vulnerability Index

| ID | Severity | Category | Location | OWASP 2025 |
|----|----------|----------|----------|------------|
| VULN-01 | CRITICAL | Path Traversal | `html/image.php:6-8` | A01 Broken Access Control |
| VULN-02 | CRITICAL | Path Traversal | `html/admin/image.php:5-9` | A01 Broken Access Control |
| VULN-03 | CRITICAL | Command Injection | `libs/poster.class.inc.php:174-177` | A05 Injection |
| VULN-04 | HIGH | SQL Injection | `libs/order.class.inc.php:133` | A05 Injection |
| VULN-05 | HIGH | Missing Authentication | `html/admin/report.php` | A01 Broken Access Control |
| VULN-06 | HIGH | Missing Authentication | `html/admin/graph.php` | A01 Broken Access Control |
| VULN-07 | HIGH | XSS (Reflected) | `html/step3.php:86-99,124` | A05 Injection |
| VULN-08 | HIGH | Information Disclosure | `html/admin/includes/about.inc.php` | A02 Security Misconfiguration |
| VULN-09 | MEDIUM | CSRF | All forms | A01 Broken Access Control |
| VULN-10 | MEDIUM | CORS Misconfiguration | `html/create.php:3` | A02 Security Misconfiguration |
| VULN-11 | MEDIUM | File Upload | `html/create.php`, `libs/verify.class.inc.php` | A01 Broken Access Control |
| VULN-12 | MEDIUM | Session Exposure | `html/includes/session.inc.php:26` | A07 Authentication Failures |
| VULN-13 | MEDIUM | Missing Security Headers | Web server config | A02 Security Misconfiguration |
| VULN-14 | MEDIUM | Missing exit() After Redirect | Multiple files | A01 Broken Access Control |
| VULN-15 | MEDIUM | Weak Cryptography | `libs/functions.class.inc.php:241-244` | A04 Cryptographic Failures |
| VULN-16 | LOW | XSS (Admin) | Multiple admin pages | A05 Injection |
| VULN-17 | LOW | Logic Bug | `libs/verify.class.inc.php:93` | A10 Mishandling Exceptional Conditions |
| VULN-18 | LOW | Logic Bug | `libs/finishoption.class.inc.php:138,152,188` | A10 Mishandling Exceptional Conditions |
| VULN-19 | LOW | Outdated Dependencies | `composer.json` | A03 Supply Chain Failures |

---

## Critical Vulnerabilities

### VULN-01: Arbitrary File Read via Public image.php

**File:** `html/image.php:4-9`
**CVSS:** 9.1 (Critical)
**OWASP:** A01 Broken Access Control

```php
if (isset($_GET['image_path'])) {
    if (file_exists($_GET['image_path'])) {
        header('Content-Type: image/jpeg');
        readfile($_GET['image_path']);   // No path validation!
    }
}
```

**Impact:** Any unauthenticated user can read ANY file on the server that the web process can access. This includes:
- `/etc/passwd` — user enumeration
- `conf/settings.inc.php` — database credentials, LDAP credentials, SMTP credentials
- Application source code
- Other application data files

**Exploit:**
```
GET /image.php?image_path=/etc/passwd
GET /image.php?image_path=../conf/settings.inc.php
```

**Fix:**
```php
if (isset($_GET['image_path'])) {
    $allowed_base = realpath(__DIR__ . '/../posters');
    $requested = realpath($_GET['image_path']);
    if ($requested && strpos($requested, $allowed_base) === 0 && file_exists($requested)) {
        $mime = mime_content_type($requested);
        if (in_array($mime, ['image/jpeg', 'image/png', 'image/gif'])) {
            header('Content-Type: ' . $mime);
            readfile($requested);
        }
    }
}
```

---

### VULN-02: Arbitrary File Read via Admin image.php (No Auth!)

**File:** `html/admin/image.php:5-9`
**CVSS:** 9.1 (Critical)

Identical code to VULN-01. **Additionally, this file does not include `session.inc.php`**, meaning it requires no authentication at all. Same fix as VULN-01.

---

### VULN-03: Command Injection in PowerPoint Processing

**File:** `libs/poster.class.inc.php:174-177`
**CVSS:** 8.1 (High → Critical in certain configs)
**OWASP:** A05 Injection

```php
$exec = "source /etc/profile && libreoffice --headless --convert-to jpg --outdir "
        . self::get_tmp_path() . " " . $filename;
$exec($exec, $output_array, $exit_status);  // Variable function call!
```

**Analysis:** Two issues here:
1. **Variable function call bug:** `$exec(...)` uses the string variable `$exec` as a function name. Since `$exec` contains the shell command string, PHP will try to call a function named `"source /etc/profile && libreoffice..."` which will fail. This is likely a bug where `exec(...)` was intended.
2. **If fixed to `exec()`:** The `$filename` is server-generated (`tmp_RANDOM.ext`), but `self::get_tmp_path()` constructs from `settings::get_poster_dir()`. If any component is attacker-influenced, command injection is possible.

**Fix:**
```php
$safe_filename = escapeshellarg($filename);
$safe_outdir = escapeshellarg(self::get_tmp_path());
$cmd = "libreoffice --headless --convert-to jpg --outdir " . $safe_outdir . " " . $safe_filename;
exec($cmd, $output_array, $exit_status);
```

---

## High Vulnerabilities

### VULN-04: SQL Injection in order.class.inc.php

**File:** `libs/order.class.inc.php:133`
**CVSS:** 7.5

```php
$sql .= "WHERE orders_id='" . $this->get_order_id() . "' LIMIT 1";
```

While other queries in this class use parameterized queries, `set_status()` concatenates the order ID directly. The order ID comes from `$_GET['order_id']` in `html/admin/order.php` and is passed to the constructor.

**Fix:** Use parameterized query:
```php
$sql .= "WHERE orders_id=:order_id LIMIT 1";
$parameters[':order_id'] = $this->get_order_id();
```

---

### VULN-05 & VULN-06: Missing Authentication on Admin Endpoints

**Files:** `html/admin/report.php`, `html/admin/graph.php`

Both files include `main.inc.php` but **do not include `session.inc.php`**. Any unauthenticated user can:
- Generate billing reports with financial data (report.php)
- View order statistics graphs (graph.php)

**Fix:** Add to both files after `require_once 'includes/main.inc.php';`:
```php
require_once 'includes/session.inc.php';
```

---

### VULN-07: Reflected XSS in step3.php

**File:** `html/step3.php:86-99, 124`

Multiple POST values echoed directly into HTML without escaping:

```php
// Line 86 - filename echoed raw
<td><?php echo $_POST['posterFileName']; ?></td>
// Line 95 - CFOP echoed raw
<td><?php echo $_POST['cfop']; ?></td>
// Line 98 - name echoed raw
<td><?php echo stripslashes($_POST['name']); ?></td>

// Line 124 - ALL POST values into hidden inputs, keys AND values unescaped
echo "<input type='hidden' name='" . $key . "' value='" . $var . "'>";
```

**Impact:** An attacker could craft a malicious form that POSTs XSS payloads to step3.php. The hidden input injection on line 124 is especially dangerous — both keys and values are unescaped.

**Fix:** Escape all output:
```php
echo htmlspecialchars($_POST['posterFileName'], ENT_QUOTES, 'UTF-8');

// For hidden inputs:
echo "<input type='hidden' name='" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
     . "' value='" . htmlspecialchars($var, ENT_QUOTES, 'UTF-8') . "'>";
```

---

### VULN-08: Sensitive Information Disclosure in About Modal

**File:** `html/admin/includes/about.inc.php:39-57`

The About modal displays:
- MySQL host, database name, username, port
- LDAP host, base DN, bind user, port
- SMTP host, port, username
- PHP version and all extensions
- Log file paths

**Impact:** Any authenticated admin user (or anyone who compromises an admin session) gains full infrastructure knowledge for lateral movement.

**Fix:** Remove or redact sensitive fields. Only show: app version, PHP version.

---

## Medium Vulnerabilities

### VULN-09: No CSRF Protection

No forms in the entire application use anti-CSRF tokens. An attacker could create a page that auto-submits forms to:
- Submit poster orders on behalf of a user
- Change order statuses (admin)
- Modify paper types and pricing (admin)
- Generate reports (admin)

**Fix:** Implement token-based CSRF protection. Generate tokens per-session and validate on every POST.

---

### VULN-10: Wildcard CORS on create.php

**File:** `html/create.php:3`

```php
header("Access-Control-Allow-Origin: *");
```

Allows any website to submit AJAX requests to the order creation endpoint.

**Fix:** Replace with specific origin or remove if not needed for cross-domain access.

---

### VULN-11: File Upload — Extension-Only Validation

**Files:** `libs/verify.class.inc.php:75-82`, `html/create.php:88`

File upload validation checks only the file extension against a whitelist. No MIME type verification, no magic byte checking, no content inspection.

**Risk:** While the file is renamed server-side to `tmp_RANDOM.ext`, if the web server is misconfigured to execute PHP in the posters directory, a `.php` file disguised with a valid extension could be dangerous. Additionally, ImageMagick processing of malicious files could trigger known vulnerabilities (ImageTragick).

**Fix:** Add MIME type validation:
```php
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['posterFile']['tmp_name']);
$allowed_mimes = ['application/pdf', 'image/jpeg', 'image/tiff', ...];
if (!in_array($mime, $allowed_mimes)) { /* reject */ }
```

---

### VULN-12: Session ID in URL

**File:** `html/includes/session.inc.php:26`

```php
elseif (!(isset($_GET['session'])) || ($_GET['session'] != $session->get_session_id())) {
```

Session IDs are passed as GET parameters in URLs, making them visible in:
- Browser history and bookmarks
- Server access logs
- Referer headers sent to external sites
- Shoulder-surfing

**Fix:** Use cookie-based session management exclusively.

---

### VULN-13: Missing Security Headers

The application does not set any security-related HTTP headers.

**Fix:** Add to web server config or a common PHP include:
```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'');
```

---

### VULN-14: Missing exit() After header() Redirects

**Files:** `html/step3.php:9`, `html/step4.php:25`, `html/includes/session.inc.php:19,24,28`, `html/admin/includes/session.inc.php:24,28,32`

After `header('Location: ...')`, code execution continues. This means authentication/authorization checks can be bypassed — the browser follows the redirect, but an attacker using curl/scripts sees the full response body.

**Fix:** Add `exit;` after every `header('Location: ...')` call.

---

### VULN-15: Weak Key Generation

**File:** `libs/functions.class.inc.php:241-244`

```php
$key = uniqid(rand(), true);
$hash = sha1($key);
```

`rand()` and `uniqid()` are not cryptographically secure. Order status keys could be predicted.

**Fix:**
```php
$key = bin2hex(random_bytes(32));
```

---

## Low Vulnerabilities

### VULN-16: XSS in Admin Pages

Multiple admin pages echo POST/GET values directly without `htmlspecialchars()`:
- `html/admin/editOrder.php` — CFOP fields, activity code
- `html/admin/addPaperType.php` — name, cost, width
- `html/admin/editPaperType.php` — name, cost, width
- `html/admin/addFinishOption.php` — name, cost, dimensions
- `html/admin/editFinishOption.php` — name, cost, dimensions
- `html/admin/otherOptions.php` — cost fields

**Fix:** Apply `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` to all output.

---

### VULN-17: Logic Bug in Dimension Verification

**File:** `libs/verify.class.inc.php:93`

```php
elseif (($poster_length < $submitted_length -1) || ($poster_width > $submitted_width)) {
//                                                      ^^^^^ should be poster_length > submitted_length
```

Checks width twice instead of checking length, allowing length discrepancies to pass validation.

---

### VULN-18: Logic Bugs in finishoption/papertype Classes

- `libs/finishoption.class.inc.php:138` — `delete()` called twice in `update()`
- `libs/finishoption.class.inc.php:152-155` — Parameter name mismatch (`:finishoptions_id` vs `:finishoption_id`)
- `libs/finishoption.class.inc.php:188` — `UPDATE` with no WHERE clause resets ALL records
- `libs/papertype.class.inc.php:179` — Same unfiltered UPDATE issue
- `libs/statistics.class.inc.php:232` — Checks `$posterTubeYes` twice instead of `$posterTubeNo`

---

### VULN-19: Outdated Dependencies

- `composer.json` requires PHP >=7.2 — PHP 7.2 reached EOL November 2020
- Twig, jQuery, Bootstrap versions should be checked for known CVEs

**Fix:** Run `composer audit` and upgrade to PHP 8.1+ minimum.

---

## Cross-Reference Analysis Summary

### Public Workflow → Data Layer (Sections 1↔5)
- User input in step2 flows through `create.php` → `verify` class → `poster` class → `functions::create_order()`. The `verify` class provides good validation for email and name, but file validation is extension-only. The `create_order()` function uses `build_insert()` (parameterized) which is safe.
- **Gap:** POST data passes through step3.php (display) and step4.php (submission) as hidden form fields with ZERO re-validation. An attacker can intercept at step3 and modify hidden fields before submitting to step4.

### Admin Panel → Data Layer (Sections 2↔5)
- Admin order management uses `order::set_status()` which has SQL injection (VULN-04).
- `order::edit()` is properly parameterized.
- Download uses `readfile()` on DB-sourced path — safe as long as DB isn't compromised.

### Auth Enforcement (Section 5 ↔ All Admin)
- **report.php and graph.php lack session.inc.php** — unauthenticated access.
- All other admin pages properly include session.inc.php.
- Session checks are solid: login flag, timeout, IP binding.
- But: no `exit()` after redirect means session check can be bypassed with curl.

### image.php Comparison (Public ↔ Admin)
- Both files are identical code — same vulnerability.
- Neither requires authentication.
- Both allow arbitrary file read.

---

## Remediation Priority

### Immediate (This Week)
1. Fix `image.php` (both public and admin) — path traversal
2. Add `session.inc.php` to `report.php` and `graph.php`
3. Add `exit;` after all `header('Location:')` redirects
4. Fix SQL injection in `order.class.inc.php:133`

### Short-Term (This Month)
5. Add `htmlspecialchars()` to all output in step3.php and admin pages
6. Add CSRF tokens to all forms
7. Remove sensitive info from about.inc.php
8. Fix CORS wildcard in create.php
9. Add MIME type validation for file uploads

### Medium-Term (This Quarter)
10. Replace `rand()`/`uniqid()` key generation with `random_bytes()`
11. Move session IDs from URL to cookies
12. Add security headers
13. Fix logic bugs in verify/finishoption/papertype classes
14. Upgrade PHP and dependencies
15. Re-validate all POST data in step4.php before order creation

---

## References

- [OWASP Top 10:2025](https://owasp.org/Top10/2025/)
- [OWASP Path Traversal](https://owasp.org/www-community/attacks/Path_Traversal)
- [OWASP Unrestricted File Upload](https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload)
- [PHP Command Injection Prevention](https://www.stackhawk.com/blog/php-command-injection/)
- [PHP Security Best Practices](https://www.acunetix.com/websitesecurity/php-security-2/)
- [5 PHP Vulnerabilities in 2025](https://tuxcare.com/blog/php-vulnerability/)
- [File Upload Attack Guide](https://hackviser.com/tactics/pentesting/web/file-upload)
- [ImageTragick Exploitation](https://www.infosecinstitute.com/resources/hacking/exploiting-imagetragick/)
- [Path Traversal Practical Guide](https://www.yeswehack.com/learn-bug-bounty/practical-guide-path-traversal-attacks)

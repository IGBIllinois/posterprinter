# Poster Printer Refactor

Collapses the 4-step wizard (8 UI files) into a 3-page flow (5 files), moves all
order state server-side, and fixes every bug identified in review.

## New flow

```
index.php  -->  order.php  --(api.php)-->  review.php  --(PRG)-->  status.php
(landing)      (one form)    (JSON only)   (confirm)              (confirmation
                                                                   + status)
```

| Old file             | Replaced by                          |
|----------------------|--------------------------------------|
| step1.php            | gone (dimensions come from the file; manual entry is the fallback) |
| step2.php            | order.php                            |
| create.php           | api.php (actions: upload, options, rushcheck, submit) |
| get_dimensions.php   | api.php action=upload                |
| step3.php            | review.php                           |
| step4.php            | review.php (confirm branch)          |
| status.php           | status.php (kept; now also the confirmation page) |
| index.php            | index.php (links updated)            |

`image.php` and everything under `includes/` and `admin/` are untouched.

## What structurally changed

1. **State lives in `$_SESSION`, not hidden form fields.** The staged order is
   written server-side by `api.php action=submit` and read by `review.php`.
   Nothing the browser sends after submit can alter price, file paths, or any
   order field. This removes, as a class: price tampering, tmp-filename path
   traversal, hidden-field XSS, and the apostrophe-truncation bug.
2. **File uploads once.** `action=upload` stores the file via
   `poster::move_tmp_file()` immediately and detects dimensions from the stored
   copy. The old flow uploaded every file twice (get_dimensions.php + create.php).
3. **Post/Redirect/Get.** review.php confirm redirects to status.php. Refreshing
   the confirmation can never create a duplicate order. A one-time token also
   blocks double-clicks on Submit Order.
4. **All business decisions are server-side**: rush determination (recomputed
   from pickupDate), lamination restriction (validated at submit, not just
   greyed out in JS), paper/finish validity for the dimensions, and total cost
   (computed fresh from DB prices). The JS versions are UX previews only.
5. **Single source of truth for the lamination rule** — the constants at the top
   of api.php. The JS reads server-provided flags; no IDs are hardcoded
   client-side anymore. (Still recommend a `no_lamination` column on
   `paperTypes` eventually; then only api.php changes.)

## Specific bug fixes carried over from the review

- Poster tube / rush order fees no longer charged on every order
  (boolean-as-string bug + isset-on-always-present-key bug).
- Duplicate `poster::create_image()` call removed; failure is logged and
  non-fatal instead of an array-offset-on-bool error.
- Dimension detection: handles pixels-per-centimeter units and missing/zero
  DPI (falls back to manual entry instead of returning 0x0).
- Rotated orders now priced on the **post-rotation length**. The old step3
  captured `$length` before the swap, so rotated orders were billed on the
  wrong dimension. Verify against a few historical rotated orders — this may
  change prices relative to the old behavior (correctly).
- AJAX error handlers now surface real HTTP status / server text instead of
  `undefined`; api.php converts uncaught exceptions to JSON so the client
  never receives an HTML fatal.
- `exit` after every `header('Location: ...')` (status.php PHP 8 fatal fixed).
- `htmlspecialchars` on all user-controlled output in review.php / status.php.
- CORS `Access-Control-Allow-Origin: *` headers removed (same-origin app).
- Radio-button null guard: submit validates a paper/finish selection exists
  before reading `.value`.
- post_max_size overflow detected and reported as "file too large" instead of
  a silent empty-POST failure.

## Assumptions to verify before deploying (I inferred the class APIs)

1. **`\IGBIllinois\session`** wraps native PHP sessions, so `$_SESSION` works
   after `new session(settings::get_session_name())` and is shared with pages
   that include `session.inc.php`. If it uses its own storage, swap my
   `$_SESSION['poster_*']` reads/writes for the class's get/set methods.
2. **`includes/session.inc.php`** provides `$session` and validates
   `$_GET['session']`/`$_POST['session']`. api.php instantiates the class
   directly (like the old create.php, which included only main.inc.php). If
   session.inc.php does more (IP pinning, timeout), consider including it in
   api.php too.
3. **`functions::create_order($db, $array)`** — I pass an array with the same
   keys the old step4 `$_POST` contained, plus `pickupDate`. If it ignores
   unknown keys you're fine; if it requires `step3`/`step4` flags, add them.
   Check whether it stores `pickupDate` — if the orders table has no column
   for it, add one (staff need it now that rush is date-driven).
4. **`poster::create_image($tmpBasename)`** returns an array with a `'THUMB'`
   key on success — matches the old usage but I've never seen the function.
5. **`poster::move_poster()`** return value: I treat `false` as failure and
   log. If it returns void, the check is harmless.
6. **`order->get_key()`** exists (status.php already used it) and
   `order->mailNewOrder()` returns truthy on success.
7. **image.php path restriction**: review.php previews reference
   `poster::get_tmp_path()`. image.php only serves files under
   `settings::get_poster_dir()`. If tmp isn't inside poster dir, previews
   404 silently — either put tmp under poster dir or add tmp path to
   image.php's allowlist. (The old step3 had the same latent issue.)

## Deploy

1. Back up the current web root and DB.
2. Drop in: index.php, order.php, api.php, review.php, status.php.
3. Remove (or rename to .bak): step1.php, step2.php, step3.php, step4.php,
   create.php, get_dimensions.php.
4. Populate `HOLIDAYS` in api.php.
5. Confirm the lamination IDs at the top of api.php against the DB:
   `SELECT id, name FROM paperTypes; SELECT id, name FROM finishOptions;`

## Test checklist

- [ ] PDF upload -> dimensions detected -> confirm -> options load
- [ ] PNG with no DPI metadata -> manual entry prompt appears
- [ ] PPT/PPTX (non-detectable type) -> manual entry prompt appears
- [ ] Oversize dimensions -> verify_dimensions message shown, options hidden
- [ ] Editing dimensions unchecks the confirm box; submit blocked until re-checked
- [ ] Canvas/watercolor paper selected -> lamination greyed out, None auto-selected;
      then also try forging finishOptionsId=2 via devtools -> server rejects
- [ ] Pickup date tomorrow -> rush fee row appears; pickup next week -> no fee;
      weekend date -> rejected
- [ ] Order WITHOUT tube/rush -> total cost excludes both fees (the big billing bug)
- [ ] Name with apostrophe (O'Brien) + comment with quotes/`<b>` -> survives to
      review and status pages intact and unrendered
- [ ] Rotated order (width > paper width) -> review shows flip notice, price uses
      final length
- [ ] Submit -> review -> Submit Order -> lands on status.php; refresh status ->
      no duplicate order; back button + resubmit -> token blocks it
- [ ] Cancel from order.php and review.php -> tmp file deleted, back to index
- [ ] File larger than post_max_size -> clear error message
- [ ] Direct GET of review.php with no staged order -> clean redirect to index

# 00 · Start with a browser and the real source

A browser receives HTML, not PHP. PHP runs on the server, queries MySQL-compatible storage and writes an HTML response. You inspect that response in DevTools Elements, then locate its template in `app/views.php` or `app/portal-views.php`. CSS chooses appearance; JavaScript attaches behavior after the document is parsed.

## Prerequisites and setup

Know tags, nesting, strings, arrays and functions. A **DOM** is the browser's object tree representing the parsed HTML. A **selector** is a pattern used to find nodes. A **viewport** is the visible layout area in CSS pixels, independent of physical screen pixels.

Use [macOS setup](../Assemble/03-local-development-macos.md), [Windows/Linux setup](../Assemble/04-local-development-windows-linux.md) or the [existing detailed guide](../docs/local-setup.md). Keep `.env` private. Browse with exactly `APP_URL`, including hostname and port. Native development starts from the repository root:

```bash
php -S localhost:8080 -t public public/router.php
```

This assumes configured PHP/database/extensions and `APP_URL=http://localhost:8080`. It does not create a database. Do not start a second server on an occupied port; use the existing local instance or choose a port and update your private APP_URL consistently.

## First ten minutes: inspect, do not edit

1. Open `/?page=home`. Select the headline in Elements. Find `.hero h1` in Styles.
2. Open [the actual view](../app/views.php) at the `home` branch. Compare the PHP with emitted HTML.
3. Open [the shell](../app/layout.php): identify `<head>`, `<main id="main">`, stylesheet, early theme script and deferred script.
4. Switch theme. Observe the `data-theme` attribute on `<html>`, not a server/database change.
5. Change DevTools width from 1440 to 390. Observe cards and public menu. If signed in with a disposable account, observe the sidebar drawer.
6. Save observations in your own notebook, with no cookies, tokens, identities or recovery codes.

## Study order

Read architecture → homepage lesson → layout/UI helpers → CSS guide → JS guide → labs → tests → contribution contract. Inspect code before attempting changes. Ask what a control means, what the server enforces and how it behaves without a mouse.

## Safety boundary

These lessons do not authorize Git writes or deployment. DevTools overrides disappear on reload; they are ideal for predictions. For a file experiment use the isolated [layout clinic](exercises/layout-clinic.html), outside `public/`. Restore only your own temporary lines after reviewing your diff; never reset or clean the repository. An approved permanent improvement should remain for review.

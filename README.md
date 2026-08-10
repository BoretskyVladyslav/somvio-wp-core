![Somvio Homepage Screenshot](./screenshot.png)

# Somvio — Cleaning Service Booking Platform

Custom WordPress child theme for **Somvio Cleaning Services**: a conversion-focused booking platform with dynamic pricing, a multi-step calculator, LatePoint scheduling, and Stripe payments.

Customers get an instant, transparent quote and can complete booking (and pay) without leaving the site. Operators get structured bookings in LatePoint plus email notifications — fewer back-and-forth messages, fewer abandoned enquiries.

---

## Business value

- **Dynamic pricing** — room counts, frequency, add-ons, and service type drive a live total before submit.
- **Step-by-step booking** — guided UX from service selection through schedule, extras, contact, and payment.
- **Stripe integration** — optional online payment with PaymentIntent confirmation; pay-on-completion remains available.
- **Ops-ready** — submissions sync into LatePoint and trigger admin/customer emails.

---

## Key features

| Feature | Description |
|--------|-------------|
| **Multi-step calculator** | Modal + full booking page flows with live price preview |
| **LatePoint integration** | Creates customers, orders, and bookings from theme REST payloads |
| **Live pricing** | Server-side rate tables localized to JS; totals recalculated on change |
| **Stripe payments** | Publishable/secret keys via `wp-config` constants or options; confirm-payment REST endpoint |
| **Conditional assets** | Page-scoped CSS/JS enqueue (booking, blog, gallery, FAQ, etc.) |
| **Performance-minded** | Minified assets when `SCRIPT_DEBUG` is off; scripts load in footer with `defer` |
| **ACF JSON** | Field groups versioned under `acf-json/` |
| **Legal & SEO pages** | Privacy, terms, cookie consent, Rank Math–friendly `title-tag` support |

---

## Tech stack

| Layer | Technology |
|-------|------------|
| CMS | WordPress 6.5+ |
| Parent theme | [GeneratePress](https://generatepress.com/) |
| Language | PHP 8.0+, vanilla JS (no bundler runtime) |
| Styling | Theme `style.css` design tokens + BEM sections |
| Fields | Advanced Custom Fields (JSON sync) |
| Booking CRM | LatePoint |
| Payments | Stripe Payment Intents (REST) |
| Fonts | Montserrat (Google Fonts, `display=swap` + preconnect) |

---

## Requirements

- WordPress **6.5+**, PHP **8.0+**
- Parent theme: **GeneratePress** (active)
- Recommended plugins: **LatePoint**, **Advanced Custom Fields**, SEO plugin (e.g. Rank Math)
- For online payments: Stripe account + API keys

---

## Setup (WordPress child theme)

1. **Install parent theme**  
   Install and activate GeneratePress.

2. **Install this child theme**  
   Copy or clone this folder into:

   ```text
   wp-content/themes/somvio-child
   ```

   Then activate **Somvio Child** under **Appearance → Themes**.

3. **Core pages**  
   On theme switch / admin init, `inc/setup-pages.php` seeds core pages (Home, Booking, Services, legal pages, etc.) when the seed version bumps. Assign menus and set the static front page if needed.

4. **Stripe (optional)**  
   In `wp-config.php` (preferred over DB options):

   ```php
   define( 'SOMVIO_STRIPE_PUBLISHABLE_KEY', 'pk_live_...' );
   define( 'SOMVIO_STRIPE_SECRET_KEY', 'sk_live_...' );
   ```

   Test keys (`pk_test_` / `sk_test_`) work the same way in staging.

5. **LatePoint**  
   Install/configure LatePoint services, agents, and locations. Theme maps service keys → LatePoint IDs (filterable in `inc/booking/latepoint.php`). Seed helpers live under `inc/booking/latepoint-seed.php`.

6. **Permalinks**  
   Visit **Settings → Permalinks** and save once after activation so REST routes resolve cleanly.

7. **Caching / PageSpeed**  
   Theme scripts use stable handles, footer placement, and the `defer` strategy so plugins (WP Rocket, LiteSpeed, Autoptimize, etc.) can Delay/Defer JS safely. Prefer loading minified assets in production (`SCRIPT_DEBUG` undefined/false).

---

## Asset pipeline

| Source | Production (default) | Debug (`SCRIPT_DEBUG` true) |
|--------|----------------------|-----------------------------|
| `assets/js/*.js` | `*.min.js` via `somvio_enqueue_theme_script()` | Unminified source |
| `style.css` | `style.min.css` (GeneratePress `generate-child` URL swap) | `style.css` |

Regenerate minified files after editing sources:

```bash
# JS (requires Node.js)
npx terser assets/js/header.js -c -m -o assets/js/header.min.js
# …repeat for each file in assets/js/

# CSS
npx clean-css-cli -o style.min.css style.css
```

Keep the WordPress theme header comment at the top of `style.css` (theme metadata). `style.min.css` should retain the same header block for consistency.

---

## Project structure (high level)

```text
somvio-child/
├── functions.php              # Boot, asset helpers, ACF JSON paths
├── style.css / style.min.css  # Design system + layout
├── assets/
│   ├── js/                    # Feature scripts (+ .min.js)
│   ├── icons/                 # Inline SVG icons
│   └── images/                # Section / hero imagery
├── inc/
│   ├── booking/               # LatePoint, Stripe, emails, bootstrap
│   ├── calculator.php         # Quote rates + REST + modal assets
│   ├── setup-pages.php        # Page seeding / identity
│   └── *.php                  # Section & page controllers
├── template-parts/            # Heroes, sections, components, emails
├── acf-json/                  # ACF field group exports
└── page-*.php / single.php    # Page templates
```

---

## Performance notes (PageSpeed)

- Conditional enqueue: heavy scripts (booking, calculator, gallery) load only on relevant views.
- `defer` on theme JS; thank-you redirect script is limited to booking/quote contexts (not sitewide).
- `filemtime` cache-busting on child-theme CSS/JS URLs.
- Images use `decoding="async"` where markup is theme-controlled.
- Fonts: preconnect to Google Fonts origins.

For green-zone scores in production, pair with a page cache, image optimization (WebP/AVIF), and Delay JS on non-critical third-party tags.

---

## License & author

Theme package for Somvio Cleaning Services.  
Author: Vladyslav Boretskyi  
Parent: GeneratePress  

---

## Screenshot

Add a homepage capture as `screenshot.png` in the theme root (also used by WordPress under **Appearance → Themes**). The placeholder at the top of this README points to that file.

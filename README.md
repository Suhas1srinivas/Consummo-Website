# Consummo Website

Marketing website for **Consummo**: demand, supply, inventory and pricing intelligence for FMCG brands and retailers.

## Run it locally

```bash
npm start
```

Then open **http://localhost:3001**.

Alternatives:

```bash
node server.js          # built-in Node server on port 3001
npx serve -l 3001 .     # any static file server works
python -m http.server 3001
```

## What's on the site

| Area | Pages |
| --- | --- |
| Home | Hero, platform showcase, Meet Nova, industry expertise, why Consummo |
| Solutions | Demand Pulse, Supply Rhythm, Inventory Harmony, Price Tempo, plus supporting solution pages |
| Industries | FMCG Brand and Retail hubs, with 16 business pages (6 FMCG categories, 10 retail formats) |
| Company | Company hub, About Consummo, Trust and Security |
| Legal | Privacy Policy, Terms of Use, Cookie Declaration |

Every business page includes before-and-after KPIs, a team-by-team view of the change, a business-specific dashboard and the Nova assistant.

## Design

* **Colours:** brand navy `#002067`, blue `#007cf4`, cyan `#00d4ff`, coral `#ff6253`, teal `#10c7a2`, purple `#6a6bf1`, gold `#f8c352`
* **Font:** Poppins (400, 500, 600, 700), self-hosted in `_astro/`
* **Motion:** `_astro/consummo-motion.css` and `_astro/consummo-motion.js` add scroll reveals, counters, the reading-progress bar, back-to-top button and the aurora hero background. All motion is turned off for visitors who prefer reduced motion.
* **Illustrations:** solution artwork lives in `_astro/consummo-art-*.svg`.

## Project structure

```
_astro/               Stylesheets, scripts, fonts, videos and illustrations
index.html            Home page
solutions/            Solution pages
industries/           FMCG and retail hubs and business pages
about/, company/, trust-security/
privacy-policy/, terms/, cookie-declaration/
search-index.json     Page list for site search
server.js             Small static server (port 3001)
```

## Security

* **`.htaccess`** (Hostinger): forces HTTPS, redirects `consummo.com` to `www`, sends security headers (HSTS, Content Security Policy, X-Frame-Options, Referrer-Policy, Permissions-Policy), blocks hidden and private files (`.git`, `README.md`, `server.js`, `api/config.php`, lead files) and serves `404.html`.
* **Forms** (`api/contact.php`): same-site check, request size limit, honeypot and timing check, per-visitor and daily limits, confirmation emails capped per address, allow-listed values, spreadsheet-safe lead files, no PHP errors shown to visitors.
* **Secrets** live only in `api/config.php` on the server, which is excluded from Git. Never commit passwords or keys.
* `server.js` sends the same headers locally, so policy problems show up before deployment.

## Before going live

* Legal pages contain highlighted placeholders that need review by your legal team.
* KPI figures on industry pages are illustrative examples.
* Add your own analytics and cookie-consent tool. The Cookie Declaration page's "change settings" button calls `window.consummoConsent.open()` if you provide it.

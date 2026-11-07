# Consummo — Always-On Decision Intelligence for Supply Chain Planning

An exact, high-fidelity replication of the [ToolsGroup](https://www.toolsgroup.com) website, completely rebranded as **Consummo**.

---

## 🚀 Quick Start

You can run the site using any local HTTP server:

### Option 1: Using Node / npm (Recommended)
```bash
# Starts local server on http://localhost:3001
npm start
```
Or directly with `npx`:
```bash
npx serve -l 3001 .
```

### Option 2: Using Python
```bash
python -m http.server 3001
```
Then visit **[http://localhost:3001](http://localhost:3001)** in your browser.

---

## 🎨 Rebranding & Design Highlights

* **Brand Name:** Fully customized to **Consummo** across all page copy, headlines, testimonials, analyst reviews, meta tags, OpenGraph, and structured JSON-LD schemas.
* **Consummo Vector Logo:** Bespoke modern vector SVG logomark designed with dynamic dual supply-chain steerage arcs, coral accent pulse, and clean geometric Poppins typography, rendering in both light navbar and dark footer contexts.
* **Exact Palette & Styling:**
  * Midnight Brand Blue: `#002067`
  * Electric Steerage Blue: `#007cf4`
  * Neon Cyan: `#00d4ff`
  * Coral Accent: `#ff6253`
  * Typography: Full Poppins font family (400, 500, 600, 700) self-hosted locally in `_astro/`.

---

## 🧩 Included Features & Interactive Components

1. **Enterprise Header & Mega-Menu:**
   * Dynamic dropdown panels for **Solutions**, **Industries**, **Resources**, and **Company**.
   * Responsive mobile navigation drawer with animated hamburger toggle.
   * Quick-search modal wired to `search-index.json`.
2. **Hero Section:**
   * Headline: *"Don't Just Plan Your Supply Chain. Steer It."*
   * Subhead: *"AI-powered demand, inventory, pricing and supply decisions in real time. Hit service, cost and margin targets, even during disruption."*
   * Interactive CTAs: *"Book a Demo"* and *"Explore the Platform"*.
3. **Customer Logo Strip:**
   * World-class enterprise clients: Aston Martin, Franke, Harley-Davidson, Mitsubishi Electric, Absolut, SKF, Kiko Milano, Thule, Karl Lagerfeld, BorgWarner, TireHub, etc.
4. **Real Results / Impact Metrics:**
   * 40–90% Workload Reduction
   * 5–10 pt Forecast Accuracy Improvement
   * 20–30% Inventory Cut
   * 3–5 pt Margin Growth
   * Up to 99% Service Levels
5. **Interactive Platform Showcase ("One Platform. Every Decision."):**
   * Interactive tabs switching between **Demand Planning**, **Supply Planning**, **PPMO**, **Strategic Planning**, **Supply Chain Visibility**, and **Retail Planning**.
6. **Agentic Planning Assistant ("Meet Decy"):**
   * Features a live interactive **Steerage Console** where visitors can click prompt chips or type questions to simulate AI-driven supply chain decision recommendations in real time.
7. **Customer Case Studies Carousel:**
   * Aston Martin, Franke, McDonald’s Mesoamérica, Boggi Milano, Sapore di Mare, Gaviota with slide transitions, key metrics, and video lightbox triggers.
8. **Industry-Specific Expertise Grid:**
   * Detailed cards for Aftermarket Parts, Discrete Manufacturing, Food & Beverage, Wholesale Distribution, and Retail.
9. **Analyst Recognition:**
   * Gartner Magic Quadrant Leader, IDC MarketScape Leader, G2 Badges, and QKS Group Matrix.
10. **Interactive "Request a Demo" Modal:**
    * Click any *"Book a Demo"* or *"Request Demo"* button to open the modal with form validation and instant confirmation.

---

## 📂 Project Directory Structure

```
├── _astro/             # Complete bundle of CSS stylesheets, web fonts (Poppins), JS modules, and 200+ high-res media/SVGs
├── index.html          # Main landing page with full interactive markup & Consummo branding
├── search-index.json   # Rebranded search index for instant client-side search
├── package.json        # Project metadata and run scripts
├── favicon.svg         # Consummo vector browser icon
├── favicon.ico         # Standard browser icon
├── apple-touch-icon.png# Mobile home-screen icon
├── og-default.png      # Social graph preview image
└── tools/              # Utility scripts used for building and local verification
```


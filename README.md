# Teckform

A WordPress agency and business theme built for Elementor: 25 Elementor widgets, a header and footer builder,
dark mode, content types for services, projects, team and testimonials, and a one-click demo import.

## Files

| File | Purpose |
|---|---|
| `style.css` | Theme header (name, version, author), color/font variables and base styles |
| `functions.php` | Theme supports, menus, single-page details, loads everything in `inc/` |
| `header.php` / `footer.php` / `index.php` | Theme templates; Teckform or Elementor Pro templates replace the header and footer |
| `inc/templates.php` | Header & Footer builder: header, footer and section templates, display rules, per-page override |
| `inc/content-types.php` | Services, Projects (with categories), Team and Testimonials, with their fields |
| `inc/mega-menu.php` | "Mega menu" field on menu items |
| `inc/dark-mode.php` | Dark mode settings, head script and colors |
| `inc/forms/` | Server side of the Contact Form, Newsletter and Login widgets; Messages and Subscribers screens |
| `inc/demo/` | One-click demo import (Appearance > Import Demo) |
| `inc/elementor/widgets.php` | Registers the widgets, their panel groups, and every file in `assets/css` and `assets/js` |
| `inc/elementor/widgets/` | One file per widget |
| `inc/elementor/global-styles.php` | Applies the Teckform palette and fonts to Elementor's Global Colors and Fonts |
| `inc/elementor/dark-mode-controls.php` | "Dark Mode" style section on Elementor containers, sections and columns |
| `assets/css/{widget}.css`, `assets/js/{widget}.js` | Each widget's own styles and script, loaded only where it's used |
| `languages/teckform.pot` | Translation template |
| `screenshot.png`, `readme.txt` | Theme preview and WordPress.org-style readme |

## Widgets

In the Elementor panel the theme's widgets are grouped at the top:

| Group | Widgets |
|---|---|
| **Teckform Sections** | Hero, Posts Grid, Portfolio Grid, Team, Testimonials Carousel, Logo Carousel, Pricing Table, FAQ, Process / Timeline, Call to Action, Countdown, Contact Form, Newsletter, Login / Register |
| **Teckform Header & Footer** | Site Logo, Nav Menu, Search, Copyright, Dark Mode Toggle |
| **Teckform Elements** | Animated Headline, Flip Box, Before / After, Image Hotspots, Popup, Feature Box |

Free Elementor already includes Counter, Video, Google Maps, Icon List and Social Icons; the demo uses those.

Every widget has full Style tab controls, follows dark mode and the global colors, and works right to left.

### Notes on specific widgets

- **Hero:** six layouts (centered, split, full background, product screenshot, animated headline and stats, split
  with form). Highlighted words rotate when you put one per line.
- **Posts Grid / Portfolio Grid / Team / Testimonials:** pull from the content types (Services, Projects, Team,
  Testimonials in the dashboard menu) or, for Team and Testimonials, items typed into the widget.
- **Contact Form:** every submission is saved under **Messages**, even if the email can't be sent.
  Many hosts need an SMTP plugin for reliable email. Exclude pages with forms from page caching.
- **Newsletter:** saves subscribers under **Messages > Subscribers** (with CSV export), or posts to a mailing
  service's form URL (e.g. Mailchimp).
- **Login / Register:** the Register tab appears when **Settings > General > Anyone can register** is on.
- **FAQ:** adds FAQ structured data for Google. Turn that on for only one FAQ per page.
- **Popup:** opens from its button, after a delay, on scroll, on exit intent, or from any link to `#popup-id`.
  Content can be a Section template.

## Header & Footer builder

1. Go to **Header & Footer > Add New Template**, give it a title, and choose its **Type**:
   - **Header** or **Footer**, with **Display on** (entire site, front page, specific pages) and **Hide on**.
     Headers can be **sticky** (all devices, desktop and tablet, or desktop only), **transparent**,
     **hide when scrolling down**, and change **background after scrolling**.
   - **Section**: a reusable design for **popups** and **mega menus**.
2. Click **Publish**, then **Edit with Elementor**.

When several templates match a page, the most specific one wins (specific pages, then front page, then entire site).
Every page and post has a **Header & Footer** box to pick a different template or hide them on that page.

**Mega menus:** in **Appearance > Menus**, open a top-level item and pick a Section template under **Mega menu**.
The Nav Menu widget shows it as a full-width dropdown on desktop and inline in the mobile menu
(width and style options are in the widget).

## Colors and fonts

The theme and all its widgets use Elementor's **Global Colors** and **Global Fonts**
(Elementor > Site Settings), so changing them there restyles the whole site. On activation, or from the notice in
the dashboard, they're set to the Teckform palette (Inter font, blue accent).

## Dark mode

Add the **Dark Mode Toggle** widget to your header or footer. Visitors' choice is remembered.
Settings are in **Appearance > Customize > Dark Mode**: on/off, mode for new visitors (light, dark, or follow
their device), and the dark colors, including dark versions of Elementor's Global Colors.
Every container, section and column also has a **Style > Dark Mode** section for its own dark colors.

## Demo import

**Appearance > Import Demo** creates the "Teckform Studio" agency site: Home, About, Services, Portfolio, Pricing,
Blog, Contact, Careers, My Account and Coming Soon pages, services, projects, team, testimonials, posts, menus,
header, footer, mega menu and a newsletter popup. Images are generated on the server, so there's nothing to license.
**Remove demo content** deletes everything it created.

## Translation and right-to-left

All text uses the `teckform` text domain; `languages/teckform.pot` is the template for translations
(regenerate it with `wp i18n make-pot . languages/teckform.pot --exclude=dev`). Styles use logical CSS properties,
so Arabic and other right-to-left sites mirror correctly.

## Adding an Elementor widget

1. Copy a widget from `inc/elementor/widgets/` (e.g. `class-feature-box.php`) and rename its class and `get_name()`.
2. List it in `teckform_elementor_widgets()` in `inc/elementor/widgets.php`.
3. Put its styles in `assets/css/{slug}.css` and any script in `assets/js/{slug}.js`; they're registered
   automatically as `teckform-{slug}`. Return those handles from `get_style_depends()` / `get_script_depends()`.
4. Return a group from `get_categories()`: `teckform-sections`, `teckform-site` or `teckform`.

Don't name widget methods `get_items`, `get_settings`, `get_config` or other names Elementor declares `final`;
that crashes the site.

> Widgets live in the theme, so pages that use them lose those widgets if a site switches to another theme.
> If you need them to survive a theme switch, move `inc/elementor/` into a small companion plugin later.

## Local development

Needs Docker Desktop. Starts WordPress with Elementor at http://localhost:8080 with the theme folder mounted live,
imports the demo, and adds two test pages (`/hero-layouts/` and `/widget-smoke-test/`).
Login details are in `dev/setup.sh`.

```bash
docker compose -f dev/docker-compose.yml up -d
docker compose -f dev/docker-compose.yml run --rm cli sh /seed/setup.sh
```

Stop it with `docker compose -f dev/docker-compose.yml down` (add `-v` to delete the site data too).
The `dev/` folder is left out of the theme zip.

## Build an uploadable zip

The zip must contain a single `teckform/` folder with the theme files inside it:

```bash
git archive --format=zip --prefix=teckform/ -o teckform.zip HEAD
```

Then upload `teckform.zip` in **Appearance > Themes > Add New > Upload Theme**.

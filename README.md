# Teckform

A lightweight WordPress starter theme built for Elementor.

## Files

| File | Purpose |
|---|---|
| `style.css` | Theme header (name, version, author) and base styles |
| `functions.php` | Theme supports, menus, Elementor locations, "install Elementor" notice |
| `header.php` / `footer.php` | Site header and footer, replaceable by Elementor Pro Theme Builder |
| `index.php` | Fallback template for every page, post and archive |
| `inc/templates.php` | Header & Footer builder: template post type, display rules, rendering |
| `assets/js/theme.js` | Sticky/transparent header scroll state and the back-to-top button |
| `assets/js/widgets.js` | Mobile menu and full-screen search behavior |
| `inc/elementor/widgets.php` | Registers the custom Elementor widgets and the "Teckform" panel category |
| `inc/elementor/widgets/` | One file per widget (`class-feature-box.php` is the example) |
| `assets/css/widgets.css` | Widget styles, loaded only on pages that use a Teckform widget |
| `screenshot.png` | Preview shown in Appearance > Themes (1200×900) |
| `readme.txt` | Theme info in WordPress.org format |

## Header & Footer builder

Requires the free Elementor plugin.

1. Create a menu in **Appearance > Menus** and set a logo in **Appearance > Customize > Site Identity**.
2. Go to **Header & Footer > Add New Template**, give it a title, and choose:
   - **Type:** Header or Footer
   - **Display on:** Entire site, Front page only, or Specific pages
   - **Hide on:** pages that should not use it
   - Header only: **Sticky** (all devices, desktop and tablet, or desktop only), **Transparent**,
     **Background after scrolling**, **Hide when scrolling down**, and **Shadow after scrolling**
3. Click **Publish**, then **Edit with Elementor** and design it with the Teckform widgets:
   **Site Logo**, **Nav Menu**, **Search**, **Copyright**, **Dark Mode Toggle**, plus any free Elementor widget.

When several templates match a page, the most specific one wins (specific pages, then front page, then entire site).
With no template, the theme's simple built-in header and footer are used.

Every page and post also has a **Header & Footer** box in its sidebar to override this for that page:
use the display rules, pick a specific template, or hide the header or footer.

## Dark mode

Add the **Dark Mode Toggle** widget to your footer (or header). Visitors' choice is remembered.
The built-in footer shows a toggle automatically.

Settings are in **Appearance > Customize > Dark Mode**: turn it on or off, choose the mode for new visitors
(light, dark, or follow their device), and set the dark colors.

Colors you pick directly on an Elementor widget stay the same in dark mode. Two ways to change them in dark mode:

- Every container, section and column has a **Style > Dark Mode** section with its own dark background, text and border color.
- Use an Elementor **Global Color** and give that color a dark version in **Customize > Dark Mode**.

The back-to-top button can be turned off in **Appearance > Customize > Teckform Options**.

## Widgets

In the Elementor panel the theme's widgets are grouped at the top:

| Group | Widgets |
|---|---|
| **Teckform Sections** | Hero |
| **Teckform Header & Footer** | Site Logo, Nav Menu, Search, Copyright, Dark Mode Toggle |
| **Teckform Elements** | Feature Box |

### Hero

The first section of a page, with six layouts: **Centered**, **Split** (text and image), **Full background**
(image or video), **Product screenshot**, **Animated headline and stats**, and **Split with form**.

Each part can be turned off: badge, headline with highlighted words (one per line to rotate them, as a slide
or typing animation), description, benefits list, two buttons, image or video, and a trust row of client logos,
customer photos with a star rating, or numbers that count up. "Automatic" settings pick what suits the layout.

For the form layout, paste the shortcode of any form plugin (Contact Form 7, WPForms, Fluent Forms...).
Without one, the card shows a button instead.

## Adding an Elementor widget

The Elementor plugin must be installed on the site; the theme only adds widgets to it.

1. Copy `inc/elementor/widgets/class-feature-box.php` to a new file, e.g. `class-pricing-card.php`.
2. In the copy, rename the class (`Pricing_Card`) and change `get_name()` to a unique slug (`teckform-pricing-card`), plus `get_title()` and `get_icon()` ([Elementor icon list](https://elementor.github.io/elementor-icons/)).
3. Change the controls in `register_controls()` and the HTML in `render()` and `content_template()`.
4. List it in `teckform_elementor_widgets()` in `inc/elementor/widgets.php`:

   ```php
   'class-pricing-card.php' => '\Teckform\Elementor\Pricing_Card',
   ```

5. Add its CSS to `assets/css/widgets.css` (or its own file registered in `inc/elementor/widgets.php`, like the Hero).
6. Return one of the groups from `get_categories()`: `teckform-sections`, `teckform-site` or `teckform`.
   New groups go in `teckform_elementor_categories()`.

> Widgets live in the theme, so pages that use them lose those widgets if a site switches to another theme.
> If you need them to survive a theme switch, move `inc/elementor/` into a small companion plugin later.

## Local development

Needs Docker Desktop. Starts WordPress with Elementor at http://localhost:8080, with the theme folder mounted live,
plus demo pages, a menu and sample header/footer templates (login details are in `dev/setup.sh`):

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

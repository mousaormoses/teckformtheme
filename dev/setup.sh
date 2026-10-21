#!/bin/sh
# Installs WordPress + Elementor and seeds demo content for testing Teckform.
# Safe to re-run: it skips the install if WordPress is already set up.
set -e

SITE_URL="http://localhost:8080"
ADMIN_USER="admin"
ADMIN_PASS="teckform-dev"

if ! wp core is-installed 2>/dev/null; then
	wp core install --url="$SITE_URL" --title="Teckform Dev" \
		--admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASS" \
		--admin_email="admin@example.test" --skip-email
fi

wp plugin install elementor --activate
wp theme activate teckform
wp rewrite structure '/%postname%/' --hard
# Skip Elementor's first-run onboarding screen.
wp option update elementor_onboarded 1

# --- Pages --------------------------------------------------------------------
lorem="<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>"
long="$lorem$lorem$lorem$lorem$lorem$lorem$lorem$lorem$lorem$lorem$lorem$lorem"

page() {
	existing=$(wp post list --post_type=page --title="$1" --field=ID --format=ids)
	if [ -n "$existing" ]; then echo "$existing"; return; fi
	wp post create --post_type=page --post_status=publish --post_title="$1" --post_content="$2" ${3:+--post_parent=$3} --porcelain
}

home=$(page "Home" "<h2>Welcome to Teckform</h2>$long")
about=$(page "About" "$long")
services=$(page "Services" "$long")
web=$(page "Web Design" "$lorem" "$services")
seo=$(page "SEO" "$lorem" "$services")
contact=$(page "Contact" "$lorem")

wp option update show_on_front page
wp option update page_on_front "$home"

# --- Menu ---------------------------------------------------------------------
if ! wp menu list --fields=slug --format=csv | grep -q "^main-menu$"; then
	wp menu create "Main Menu"
	wp menu item add-post main-menu "$home" --title="Home"
	wp menu item add-post main-menu "$about"
	parent=$(wp menu item add-post main-menu "$services" --porcelain)
	wp menu item add-post main-menu "$web" --parent-id="$parent"
	wp menu item add-post main-menu "$seo" --parent-id="$parent"
	wp menu item add-post main-menu "$contact"
fi
menu_id=$(wp menu list --fields=term_id,slug --format=csv | grep ",main-menu$" | cut -d, -f1)

# --- Header & footer templates ------------------------------------------------
template() {
	# $1 title, $2 type, $3 display, $4 elementor json
	existing=$(wp post list --post_type=teckform_template --title="$1" --field=ID --format=ids)
	id=${existing:-$(wp post create --post_type=teckform_template --post_status=publish --post_title="$1" --porcelain)}
	wp post meta update "$id" _teckform_type "$2" >/dev/null
	wp post meta update "$id" _teckform_display "$3" >/dev/null
	wp post meta update "$id" _elementor_edit_mode builder >/dev/null
	wp post meta update "$id" _elementor_template_type wp-post >/dev/null
	wp post meta update "$id" _elementor_data "$4" >/dev/null
	echo "$id"
}

header_json='[{"id":"a1a1a1a","elType":"container","isInner":false,"settings":{"flex_direction":"row","flex_justify_content":"space-between","flex_align_items":"center","flex_gap":{"column":"24","row":"24","unit":"px"},"background_background":"classic","background_color":"#FFFFFF","teckform_dark_bg":"#0B1120","padding":{"unit":"px","top":"16","right":"24","bottom":"16","left":"24","isLinked":false}},"elements":[{"id":"b1b1b1b","elType":"widget","widgetType":"teckform-site-logo","settings":{},"elements":[]},{"id":"c1c1c1c","elType":"widget","widgetType":"teckform-nav-menu","settings":{"menu":"'"$menu_id"'","align":"flex-end","_flex_size":"grow"},"elements":[]},{"id":"d1d1d1d","elType":"widget","widgetType":"teckform-search","settings":{},"elements":[]}]}]'

home_header_json='[{"id":"a2a2a2a","elType":"container","isInner":false,"settings":{"flex_direction":"row","flex_justify_content":"space-between","flex_align_items":"center","padding":{"unit":"px","top":"20","right":"24","bottom":"20","left":"24","isLinked":false}},"elements":[{"id":"b2b2b2b","elType":"widget","widgetType":"teckform-site-logo","settings":{"name_color":"#FFFFFF"},"elements":[]},{"id":"c2c2c2c","elType":"widget","widgetType":"teckform-nav-menu","settings":{"menu":"'"$menu_id"'","align":"flex-end","item_color":"#FFFFFF","item_color_tablet":"#1F2937","_flex_size":"grow"},"elements":[]},{"id":"d2d2d2d","elType":"widget","widgetType":"teckform-search","settings":{"icon_color":"#FFFFFF"},"elements":[]}]}]'

footer_json='[{"id":"a3a3a3a","elType":"container","isInner":false,"settings":{"flex_direction":"row","flex_justify_content":"space-between","flex_align_items":"center","flex_wrap":"wrap","background_background":"classic","background_color":"#111827","padding":{"unit":"px","top":"40","right":"24","bottom":"40","left":"24","isLinked":false}},"elements":[{"id":"b3b3b3b","elType":"widget","widgetType":"teckform-copyright","settings":{"color":"#D1D5DB"},"elements":[]},{"id":"c3c3c3c","elType":"widget","widgetType":"teckform-nav-menu","settings":{"menu":"'"$menu_id"'","layout":"vertical","breakpoint":"none","item_color":"#D1D5DB","item_hover_color":"#FFFFFF"},"elements":[]},{"id":"d3d3d3d","elType":"widget","widgetType":"teckform-dark-mode-toggle","settings":{},"elements":[]}]}]'

site_header=$(template "Site Header" header entire "$header_json")
wp post meta update "$site_header" _teckform_sticky 1 >/dev/null

home_header=$(template "Home Header" header front "$home_header_json")
wp post meta update "$home_header" _teckform_sticky 1 >/dev/null
wp post meta update "$home_header" _teckform_transparent 1 >/dev/null
wp post meta update "$home_header" _teckform_scrolled_bg "#111827" >/dev/null

site_footer=$(template "Site Footer" footer entire "$footer_json")
wp post meta update "$site_footer" _teckform_exclude "[$contact]" --format=json >/dev/null

# Per-page control: the Web Design page hides the header.
wp post meta update "$web" _teckform_header hide >/dev/null

# Dark mode: give Elementor's "Primary" global color a dark version.
wp theme mod set teckform_dark_elementor_primary "#93c5fd" >/dev/null

# Build the home page with Elementor: a dark full-width hero for the transparent header to sit on.
home_json='[{"id":"e1e1e1e","elType":"container","isInner":false,"settings":{"content_width":"full","min_height":{"unit":"vh","size":80},"flex_justify_content":"center","flex_align_items":"center","background_background":"classic","background_color":"#1E3A8A"},"elements":[{"id":"f1f1f1f","elType":"widget","widgetType":"heading","settings":{"title":"Transparent header demo","header_size":"h1","align":"center","title_color":"#FFFFFF"},"elements":[]}]},{"id":"e2e2e2e","elType":"container","isInner":false,"settings":{"padding":{"unit":"px","top":"60","right":"24","bottom":"60","left":"24","isLinked":false}},"elements":[{"id":"f2f2f2f","elType":"widget","widgetType":"text-editor","settings":{"editor":"'"$long"'"},"elements":[]}]}]'
wp post meta update "$home" _elementor_edit_mode builder >/dev/null
wp post meta update "$home" _elementor_template_type wp-page >/dev/null
wp post meta update "$home" _elementor_data "$home_json" >/dev/null

# --- Hero widget demo: one page with all six layouts -----------------------------
hero() {
	# $1 element id suffix, $2 layout, $3 extra settings (JSON fragment, may be empty)
	printf '{"id":"h%s0000","elType":"container","isInner":false,"settings":{"content_width":"full","padding":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":true}},"elements":[{"id":"w%s0000","elType":"widget","widgetType":"teckform-hero","settings":{"layout":"%s"%s},"elements":[]}]}' "$1" "$1" "$2" "${3:+,$3}"
}

heroes_json="[$(hero 1 centered ''),$(hero 2 split ''),$(hero 3 background '"title_words":"stand out"'),$(hero 4 screenshot '"browser_frame":"yes","title_words":"one place"'),$(hero 5 animated ''),$(hero 6 form '"media_side":"left"')]"

heroes=$(page "Hero Layouts" "")
wp post meta update "$heroes" _elementor_edit_mode builder >/dev/null
wp post meta update "$heroes" _elementor_template_type wp-page >/dev/null
wp post meta update "$heroes" _elementor_data "$heroes_json" >/dev/null

wp elementor flush-css 2>/dev/null || true

echo "Done: $SITE_URL (user: $ADMIN_USER)"
echo "Hero demo: $SITE_URL/hero-layouts/"
echo "Templates: header=$site_header home_header=$home_header footer=$site_footer contact_page=$contact"

#!/bin/sh
# Installs WordPress + Elementor, activates Teckform and imports the full agency demo
# (the same one-click import buyers get in Appearance > Import Demo).
# Safe to re-run: the import removes its earlier content first.
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

# --- The agency demo -------------------------------------------------------------
wp eval 'var_dump( teckform_demo_import() );'

# --- Test pages: all Hero layouts, and every widget with default settings ----------
hero() {
	# $1 element id suffix, $2 layout, $3 extra settings (JSON fragment, may be empty)
	printf '{"id":"h%s0000","elType":"container","isInner":false,"settings":{"content_width":"full","padding":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":true}},"elements":[{"id":"w%s0000","elType":"widget","widgetType":"teckform-hero","settings":{"layout":"%s"%s},"elements":[]}]}' "$1" "$1" "$2" "${3:+,$3}"
}

heroes_json="[$(hero 1 centered ''),$(hero 2 split ''),$(hero 3 background '"title_words":"stand out"'),$(hero 4 screenshot '"browser_frame":"yes","title_words":"one place"'),$(hero 5 animated ''),$(hero 6 form '"media_side":"left"')]"

heroes=$(wp post list --post_type=page --name=hero-layouts --field=ID --format=ids)
heroes=${heroes:-$(wp post create --post_type=page --post_status=publish --post_title="Hero Layouts" --post_name=hero-layouts --porcelain)}
wp post meta update "$heroes" _elementor_edit_mode builder >/dev/null
wp post meta update "$heroes" _elementor_template_type wp-page >/dev/null
wp post meta update "$heroes" _elementor_data "$heroes_json" >/dev/null

wp eval-file /seed/smoke.php
wp elementor flush-css 2>/dev/null || true

echo "Done: $SITE_URL (user: $ADMIN_USER)"
echo "Test pages: $SITE_URL/hero-layouts/ and $SITE_URL/widget-smoke-test/"

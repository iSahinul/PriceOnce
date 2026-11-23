<?php
/**
 * Plugin Name:       Live Price Shortcodes
 * Description:       Type a price once in a JSON store and render it everywhere with shortcodes — with a checked-date stamp and per-unit figures (per-ounce, per-serving, per-month) computed at render time, so published numbers never go stale.
 * Version:           0.1.0
 * Author:            Sahinul Islam Laskar
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       live-price-shortcodes
 *
 * Store format (prices.json — see prices.example.json):
 * {
 *   "products": {
 *     "my-product": {
 *       "name": "My Product", "price": 299.95, "currency": "USD",
 *       "store": "Amazon", "url": "https://example.com/product",
 *       "checked_date": "2026-11-07", "unit_oz": 12, "unit_grams": 340
 *     }
 *   }
 * }
 *
 * Shortcodes:
 *   [lps_price id="my-product"]                       -> "$299.95 at Amazon · checked Nov 7, 2026"
 *   [lps_peroz id="my-product"]                       -> "$25.00"  (price / unit_oz)
 *   [lps_perunit id="my-product" amount="18"]         -> cost of 18 g at unit_grams
 *   [lps_permonth id="my-product" dose="18" perday="2" days="30"]
 *
 * An unknown id, a missing price, or a missing unit size renders an empty
 * string — never a stale typed number.
 */

if (!defined('ABSPATH')) exit;

/* Path to the JSON price store. Default: prices.json in this plugin's
   folder. Override in wp-config.php with LPS_STORE_PATH, or at runtime
   with the 'lps_store_path' filter. */
function lps_store_path() {
    if (defined('LPS_STORE_PATH') && LPS_STORE_PATH) return LPS_STORE_PATH;
    return apply_filters('lps_store_path', plugin_dir_path(__FILE__) . 'prices.json');
}

function lps_store() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $path = lps_store_path();
    $cache = array();
    if (is_readable($path)) {
        $raw = file_get_contents($path);
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['products']) && is_array($data['products'])) {
            $cache = $data['products'];
        }
    }
    return $cache;
}

function lps_product($id) {
    if ($id === '') return null;
    $store = lps_store();
    if (empty($store[$id]) || !is_array($store[$id])) return null;
    $p = $store[$id];
    if (!isset($p['price']) || !is_numeric($p['price'])) return null;
    return $p;
}

function lps_currency_symbol($code) {
    $map = array('USD' => '$', 'INR' => '₹', 'EUR' => '€', 'GBP' => '£',
                 'AUD' => 'A$', 'CAD' => 'C$', 'JPY' => '¥');
    $code = strtoupper((string) $code);
    return isset($map[$code]) ? $map[$code] : ($code !== '' ? $code . ' ' : '$');
}

function lps_money($x, $currency) {
    return lps_currency_symbol($currency) . number_format((float) $x, 2, '.', ',');
}

function lps_unit_oz($p, $attr_oz) {
    if (is_numeric($attr_oz) && (float) $attr_oz > 0) return (float) $attr_oz;
    if (isset($p['unit_oz']) && is_numeric($p['unit_oz'])) return (float) $p['unit_oz'];
    if (isset($p['unit_grams']) && is_numeric($p['unit_grams']) && (float) $p['unit_grams'] > 0) {
        return (float) $p['unit_grams'] / 28.349523125;
    }
    return 0;
}

function lps_unit_grams($p) {
    if (isset($p['unit_grams']) && is_numeric($p['unit_grams'])) return (float) $p['unit_grams'];
    if (isset($p['unit_oz']) && is_numeric($p['unit_oz'])) return (float) $p['unit_oz'] * 28.349523125;
    return 0;
}

/* [lps_price id="..."] — "$299.95 at Amazon · checked Nov 7, 2026" */
function lps_price_shortcode($atts) {
    $atts = shortcode_atts(array('id' => ''), is_array($atts) ? $atts : array());
    $p = lps_product($atts['id']);
    if (!$p) return '';
    $cur = isset($p['currency']) ? $p['currency'] : 'USD';
    $out = lps_money($p['price'], $cur);
    if (!empty($p['store'])) $out .= ' at ' . esc_html($p['store']);
    if (!empty($p['checked_date'])) {
        $ts = strtotime($p['checked_date']);
        if ($ts) $out .= ' &middot; checked ' . date('M', $ts) . ' ' . date('j', $ts) . ', ' . date('Y', $ts);
    }
    return '<span class="lps-price">' . $out . '</span>';
}
add_shortcode('lps_price', 'lps_price_shortcode');

/* [lps_peroz id="..." oz="..."] — price per ounce */
function lps_peroz_shortcode($atts) {
    $atts = shortcode_atts(array('id' => '', 'oz' => ''), is_array($atts) ? $atts : array());
    $p = lps_product($atts['id']);
    if (!$p) return '';
    $oz = lps_unit_oz($p, $atts['oz']);
    if ($oz <= 0) return '';
    $cur = isset($p['currency']) ? $p['currency'] : 'USD';
    return '<span class="lps-peroz">' . lps_money((float) $p['price'] / $oz, $cur) . '</span>';
}
add_shortcode('lps_peroz', 'lps_peroz_shortcode');

/* [lps_perunit id="..." amount="18"] — cost of `amount` grams at the
   product's unit size (e.g. one 18 g dose of coffee). */
function lps_perunit_shortcode($atts) {
    $atts = shortcode_atts(array('id' => '', 'amount' => ''), is_array($atts) ? $atts : array());
    $p = lps_product($atts['id']);
    if (!$p) return '';
    $grams = lps_unit_grams($p);
    $amount = is_numeric($atts['amount']) ? (float) $atts['amount'] : 0;
    if ($grams <= 0 || $amount <= 0) return '';
    $cur = isset($p['currency']) ? $p['currency'] : 'USD';
    return '<span class="lps-perunit">' . lps_money((float) $p['price'] / $grams * $amount, $cur) . '</span>';
}
add_shortcode('lps_perunit', 'lps_perunit_shortcode');

/* [lps_permonth id="..." dose="18" perday="2" days="30"] — running cost
   of a daily habit, computed from the live price. */
function lps_permonth_shortcode($atts) {
    $atts = shortcode_atts(array('id' => '', 'dose' => '18', 'perday' => '2', 'days' => '30'),
                           is_array($atts) ? $atts : array());
    $p = lps_product($atts['id']);
    if (!$p) return '';
    $grams = lps_unit_grams($p);
    $dose = is_numeric($atts['dose']) ? (float) $atts['dose'] : 18;
    $perday = is_numeric($atts['perday']) ? (float) $atts['perday'] : 2;
    $days = is_numeric($atts['days']) ? (float) $atts['days'] : 30;
    if ($grams <= 0 || $dose <= 0 || $perday <= 0 || $days <= 0) return '';
    $cur = isset($p['currency']) ? $p['currency'] : 'USD';
    $cost = (float) $p['price'] / $grams * $dose * $perday * $days;
    return '<span class="lps-permonth">' . lps_money($cost, $cur) . '</span>';
}
add_shortcode('lps_permonth', 'lps_permonth_shortcode');

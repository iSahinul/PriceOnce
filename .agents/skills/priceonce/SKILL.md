---
name: priceonce
description: Use when writing or editing WordPress content for a site that runs the PriceOnce plugin — inserting prices with the [po_price], [po_peroz], [po_perunit] and [po_permonth] shortcodes instead of typed numbers, adding or updating products in the prices.json store, or reviewing content for stale typed prices. Also use when someone asks how PriceOnce works or how to set it up.
---

# PriceOnce — write prices once, render them everywhere

PriceOnce is a WordPress plugin (github.com/iSahinul/PriceOnce) that keeps
every product price in one JSON store. Articles contain shortcodes, never
typed numbers, so a price update is one edit in the store — every post,
page and guide is current on the next page load, and each rendered price
carries the date it was last checked.

## The one rule

**Never type a price, or any number derived from a price, into content.**
Not in sentences, not in tables, not in FAQs. If a number comes from a
price, it comes from a shortcode. A typed price is a stale price waiting
to happen, and derived figures (per-ounce, per-serving, monthly cost)
must be computed from the store, never worked out by hand.

## Shortcodes

| Shortcode | Renders |
|---|---|
| `[po_price id="product-id"]` | The price, store name and checked date: `$299.95 at Example Store · checked Nov 7, 2026` |
| `[po_peroz id="product-id"]` | Price ÷ unit size, e.g. `$1.58` per ounce (requires the entry's `unit` field) |
| `[po_perunit id="product-id" amount="18"]` | Cost of `amount` grams/units at the entry's unit size |
| `[po_permonth id="product-id" dose="18" perday="2" days="30"]` | Monthly running cost: price ÷ unit size × dose × per-day × days |

An unknown `id` renders **empty** — no error, no placeholder. Before
publishing, check every shortcode's id exists in the store.

## The store (prices.json)

One JSON object, one key per product:

```json
{
  "example-espresso-machine": {
    "name": "Example Espresso Machine",
    "price": 299.95,
    "currency": "USD",
    "store": "Example Store",
    "url": "https://example.com/product",
    "checked": "2026-11-07",
    "unit_size": 1,
    "unit": "unit"
  },
  "example-coffee-beans": {
    "name": "Example Coffee Beans 340 g",
    "price": 18.99,
    "currency": "USD",
    "store": "Example Roasters",
    "url": "https://example.com/beans",
    "checked": "2026-11-07",
    "unit_size": 12,
    "unit": "oz"
  }
}
```

Field rules:
- `price` — a number, in the entry's `currency` (USD, EUR, GBP, INR and
  JPY have symbols; anything else renders as a `CODE 123.45` prefix).
- `checked` — the date a human last verified this price on the store
  page, `YYYY-MM-DD`. Never set it to today unless the price was
  actually checked today.
- `unit_size` + `unit` — required for the math shortcodes. `unit_size`
  is how much product the price buys (e.g. 12 for a 12 oz bag).
- Store location: the plugin folder by default; overridable with the
  `PO_STORE_PATH` constant in wp-config.php or the `po_store_path`
  filter.

## Adding a product (workflow)

1. Verify the price on the store page itself — never copy a price from
   another article or a search snippet.
2. Add the entry to prices.json with today's date as `checked` **only
   if you verified it today**.
3. Validate the JSON (one trailing comma breaks the whole store).
4. In the content, refer to the product only through shortcodes.
5. Preview: confirm each shortcode renders a value (empty output means
   a wrong id or broken JSON).

## Updating a price

Edit only the `price` and `checked` fields of that product's entry.
Nothing in any article changes — that is the point of the system.

## Reviewing existing content

When reviewing a draft or an old post for a PriceOnce site, flag:
- Any typed currency amount in the body, tables or FAQ answers.
- Any hand-computed derived figure ("works out to $1.50 per ounce").
- Shortcodes whose ids are missing from the store.
- Entries whose `checked` date is old — list them for re-verification
  rather than silently trusting them.

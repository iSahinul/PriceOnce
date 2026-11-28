# AGENTS.md — PriceOnce

A WordPress plugin (single PHP file) that renders prices from one JSON
store via shortcodes: `[po_price]`, `[po_peroz]`, `[po_perunit]`,
`[po_permonth]`. Unknown product ids render empty — never a stale number.

## Working in this repo
- The plugin is `priceonce.php`; the store format is documented in the
  README ("The store format") and shown in `prices.example.json`.
- The user-facing skill for this project lives in
  `.agents/skills/priceonce/SKILL.md` (linked from `.claude/skills/priceonce`) —
  read it before editing prices or advising on content.

## House rules
- **Never type a price** in content when a shortcode can render it, and
  never type a derived figure (per-ounce, per-month) — the shortcodes
  compute them from the store.
- Keep the plugin dependency-free: one PHP file, no build step.
- WordPress coding conventions; GPL-2.0-or-later stays.

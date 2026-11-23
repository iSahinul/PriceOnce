# Contributing to PriceOnce

Thanks for considering it. This is a small project with a few firm
house rules — they exist because the tool runs on real sites and real
data.

## Ground rules

- **Never type a price** in content when a shortcode can render it, and
  never type a derived figure — the shortcodes compute them.
- Keep the plugin dependency-free: one PHP file, no build step.
- WordPress coding conventions; the license stays GPL-2.0-or-later.

## Getting set up

Clone the repo, then run the tests before changing anything:

```bash
php -l priceonce.php   # syntax check (no test suite yet)
```

## Making a change

1. Open an issue first for anything bigger than a typo, so the direction
   can be agreed before you spend time on it.
2. Keep changes small and focused; one topic per pull request.
3. Add or update tests when behaviour changes, and make sure the full
   suite passes.
4. Never commit credentials, keys, session files, personal data or
   machine-specific paths. If a change needs a secret, it must come from
   the environment or a local file that stays out of git.
5. Update the README and CHANGELOG when behaviour changes.

## Reporting bugs

Use the bug-report issue form. The most useful reports say what was run,
what happened, and what was expected instead.

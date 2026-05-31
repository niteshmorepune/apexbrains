Show current project status and what's in flight.

The build is feature-complete. All 6 phases are done and all 4 portals have been audited screen-by-screen against Figma and aligned (as of 2026-05-29). The project is now in **bugfix / polish mode**, not net-new screens.

Recent polish (2026-05-31):
- Bugfixes: leaderboard Top-3 podium now renders with 1–3 ranked students (was gated ≥3) + period filter (month/week) actually applied; Franchise Performance click-to-sort column headers; Resource Library level-group filter wired up in the controller.
- **Responsive pass (all 4 portals):** Admin + Franchise are fully responsive (off-canvas drawer below `lg`, responsive grids, horizontal-scroll tables); Student + External now render as a centered, framed `md:max-w-md` app column on desktop (no longer stretched edge-to-edge), unchanged on mobile. See the "Responsive layout system" entry in `memory/feedback_patterns.md` for the conventions to follow on any new screen.

When asked for status, report:
1. Confirm the build is complete (Phases 1–6 + 4-portal Figma audit pass + 2026-05-31 responsive pass for Admin/Franchise).
2. From `git log`, summarize what was last fixed/changed and whether it's deployed (a push to `main` triggers the Hostinger deploy via GitHub Actions).
3. Any open follow-ups from `memory/project_overview.md` → "Known remaining QA items", plus open client decisions in memory (e.g. `project_commission_rule.md` — whether commission must be strictly payment-based vs the current expected-revenue fallback).
4. Any uncommitted/unpushed work (`git status`; compare local `main` to `origin/main`).

Active login views (always confirm which blade a route renders before editing):
- Admin → `resources/views/admin/login.blade.php` (NOT `auth/admin-login.blade.php`, which is dead)
- Franchise → `resources/views/auth/franchise-login.blade.php`
- Student + External → `resources/views/auth/login.blade.php`

Workflow for a new bug/request: reproduce locally (set PATH, `php artisan serve`, log in as a demo user, hit the page), read `storage/logs/laravel.log` for the real error, fix, then `php artisan view:clear && php artisan view:cache` to catch Blade compile issues before pushing. See `memory/feedback_patterns.md` for the full gotcha list.

Show current project status and what's in flight.

The build is feature-complete. All 6 phases are done and all 4 portals have been audited screen-by-screen against Figma and aligned (as of 2026-05-29). The project is now in **bugfix / polish mode**, not net-new screens.

When asked for status, report:
1. Confirm the build is complete (Phases 1–6 + 4-portal Figma audit pass).
2. From `git log`, summarize what was last fixed/changed and whether it's deployed (a push to `main` triggers the Hostinger deploy via GitHub Actions).
3. Any open follow-ups from `memory/project_overview.md` → "Known remaining QA items".
4. Any uncommitted/unpushed work (`git status`; compare local `main` to `origin/main`).

Active login views (always confirm which blade a route renders before editing):
- Admin → `resources/views/admin/login.blade.php` (NOT `auth/admin-login.blade.php`, which is dead)
- Franchise → `resources/views/auth/franchise-login.blade.php`
- Student + External → `resources/views/auth/login.blade.php`

Workflow for a new bug/request: reproduce locally (set PATH, `php artisan serve`, log in as a demo user, hit the page), read `storage/logs/laravel.log` for the real error, fix, then `php artisan view:clear && php artisan view:cache` to catch Blade compile issues before pushing. See `memory/feedback_patterns.md` for the full gotcha list.

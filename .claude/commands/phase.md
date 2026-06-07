Show current project status and what's in flight.

The build is feature-complete (all 6 phases done). The project is in **bugfix / polish + Figma-match mode**, not net-new screens.

**Latest round (2026-06-07) — Student + External portals re-matched to Figma & live:** The client reported the Student/External screens did NOT match Figma (the earlier 2026-05-29 "alignment" had left a portal-wide green/blue top app-bar that exists on no Figma frame). Both mobile portals were rebuilt: removed the top app-bar → light page bg (`bg-stu-bg` token) + per-screen `<x-student-header>` (back+title) + emoji bottom nav (green active for student / blue for external) + `@stack` added to both layouts; every internal frame (Student 30–58) and external frame (External Student 38–54) rebuilt; primary CTAs are blue (`bg-fran`). Also fixed self-practice (was 100% broken: form↔validation↔schema mismatch + migration `2026_06_07_000001`), competition-practice result route typo, global-exam visibility, profile-password edge case, and the home brand logo (now `$appSettings` logo / color wordmark like login). Added `StudentWalkthroughSeeder` (idempotent, seeds arjun@student.in at level 5 + external@test.in; run manually, NOT in DatabaseSeeder). Deployed via commits 53a133ae → ff71e270 → 60212607. **Student portal is in TEAM TESTING — awaiting comments; do not generate the Student/External Walkthrough PDFs until feedback is in.** See `memory/project_status_2026-06.md` and `memory/student_external_design.md`.

Recent polish (2026-05-31):
- Bugfixes: leaderboard Top-3 podium now renders with 1–3 ranked students (was gated ≥3) + period filter (month/week) actually applied; Franchise Performance click-to-sort column headers; Resource Library level-group filter wired up in the controller.
- **Responsive pass (all 4 portals):** Admin + Franchise are fully responsive (off-canvas drawer below `lg`, responsive grids, horizontal-scroll tables); Student + External now render as a centered, framed `md:max-w-md` app column on desktop (no longer stretched edge-to-edge), unchanged on mobile. See the "Responsive layout system" entry in `memory/feedback_patterns.md` for the conventions to follow on any new screen.

Recent bugfix round (2026-06-02) — mostly "scaffolded UI that was never wired to a backend"; see `memory/feedback_patterns.md` for the patterns:
- Admin Levels page + dashboard chart counted students from the `student_levels` history table (only filled on promotion) → showed 0; now count by `students.current_level_id`.
- `question_banks` schema fixed: `correct_answer` enum→nullable, `question_category` enum→`varchar(100)` nullable. This had silently broken ALL question creation (manual add, audio gen, import) → "0 questions".
- **Question bulk import** added (CSV/Excel via `maatwebsite/excel`): `QuestionImportController` + `App\Imports\QuestionsImport`, downloadable template, per-row error report, imported as approved, maps level by NUMBER. Replaced the dead PDF-upload stub.
- Audio question generator: validation now matches the form; the play button reads the question aloud via the **browser Web Speech API** (no server audio/MP3; voice/speed/pause not persisted).
- Levels **Assigned Book** fully wired: `levels.book_resource_id` FK → `resource_files`, `Level::book()`, edit dropdown + save + show download link.
- Franchise document upload persists into `franchise_documents` (dedicated `franchises.documents` endpoint) + shows View links.
- Error pages show the uploaded logo; deploy git step hardened to `git reset --hard` (see `memory/project_deploy.md`).

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

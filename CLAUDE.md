# Apex Brains Academy — Claude Code Guide

## Project
Multi-tenant abacus coaching platform. Laravel 12, PHP 8.3, MySQL 8.4.
4 portals: Admin | Franchise | Student (internal) | External (competition-only).
GitHub: https://github.com/niteshmorepune/apexbrains | Deploy: Hostinger via GitHub Actions.

## Phase Status
- [x] Phase 1 — Foundation (migrations, models, routes, layouts, seeders, CI/CD)
- [x] Phase 2 — Admin Panel (19 screens)
- [x] Phase 3 — Franchise Panel
- [ ] Phase 4 — Student Portal
- [ ] Phase 5 — External Portal
- [ ] Phase 6 — Polish & Deploy

## Windows Dev Environment
PHP and Composer require PATH to be set each PowerShell session:
```powershell
$env:PATH = "C:\tools\php83;C:\ProgramData\ComposerSetup\bin;C:\Program Files\MySQL\MySQL Server 8.4\bin;" + $env:PATH
```
MySQL service: `MySQL84` (NOT XAMPP — MySQL 8.4 standalone on port 3306).

## Common Commands
```powershell
# Start dev server
php artisan serve --port=8000

# Fresh migration + seed
php artisan migrate:fresh --seed

# Build assets
npm run build

# Watch assets
npm run dev

# Clear all caches
php artisan optimize:clear
```

## Tech Stack Constraints
- **Tailwind v4** via `@tailwindcss/vite` — NO `tailwind.config.js`. Colors in `resources/css/app.css` under `@theme {}`.
- **Alpine.js v3** for interactivity. Already imported in `resources/js/app.js`.
- **Laravel 12** — No `Kernel.php`. Middleware in `bootstrap/app.php` via `->withMiddleware()`.
- **Spatie Laravel Permission** for roles. Always use `hasRole()` / `can()`.
- Use `{{ }}` (never `{!! !!}`) unless rendering trusted HTML.

## Portal Colors & Middleware
| Portal | Header BG | Tailwind class | Middleware alias | Route prefix |
|---|---|---|---|---|
| Admin | #1A2332 (dark navy) | `bg-admin` | `admin` | `/admin` |
| Franchise | #1A73E8 (blue) | `bg-fran` | `franchise` | `/franchise` |
| Student | #2ECC71 (GREEN) | `bg-stu` | `internal.student` | `/student` |
| External | #1A73E8 (blue) | `bg-fran` | `external.student` | `/external` |

Student portal header is **GREEN** (`bg-stu`). Never dark navy.

## Route Files
- `routes/web.php` — root redirect, `/login`, `/logout`, `/verify/{code}`
- `routes/admin.php` — all `/admin/*` routes
- `routes/franchise.php` — all `/franchise/*` routes
- `routes/student.php` — all `/student/*` routes (internal only)
- `routes/external.php` — all `/external/*` routes (external only)

## Critical Rules (NEVER violate)
1. **NO ATTENDANCE MODULE** — no table, controller, route, or view. Ever.
2. **Audio Question Generator** — Admin panel ONLY. Franchise panel does NOT have it.
3. **External students** — NEVER access `/student/*` routes. Internal students NEVER access `/external/*`.
4. **`audit_logs` table** — immutable. Has `created_at` only, NO `updated_at`. Use `AuditLogger::log()`.
5. **Multi-tenancy** — `FranchiseTenantScope` auto-filters by `franchise_id` for `franchise_admin` role.
6. **No raw DB queries** with user input. Always Eloquent or parameterized.
7. **Rate limiting** — `throttle:6,1` on all login routes.
8. **Exam integrity** — store `ip_address`, `user_agent`, `tab_switch_count` on every attempt.

## Demo Users (password: `password`)
| Email | Role | Type |
|---|---|---|
| admin@apexbrains.in | super_admin | — |
| kothrud@apexbrains.in | franchise_admin | — |
| arjun@student.in | student | internal |
| external@test.in | student | external |

## Key File Paths
| What | Where |
|---|---|
| Brand colors (Tailwind) | `resources/css/app.css` → `@theme {}` |
| Middleware registration | `bootstrap/app.php` → `->withMiddleware()` |
| Middleware classes | `app/Http/Middleware/` |
| Models | `app/Models/` |
| Tenant scope | `app/Models/Scopes/FranchiseTenantScope.php` |
| Audit logger | `app/Services/AuditLogger.php` |
| Layouts | `resources/views/layouts/` |
| Components | `resources/views/components/` |
| CI/CD workflow | `.github/workflows/deploy.yml` |

## View Naming Convention
- Admin views: `resources/views/admin/{feature}/{action}.blade.php`
- Franchise views: `resources/views/franchise/{feature}/{action}.blade.php`
- Student views: `resources/views/student/{feature}/{action}.blade.php`
- External views: `resources/views/external/{feature}/{action}.blade.php`
- Always extend the matching layout: `@extends('layouts.admin')` etc.

## Database
- Local: MySQL 8.4, host 127.0.0.1:3306, db: `apexbrains`
- `students` table is separate from `users`. Students belong to a franchise via `franchise_id`.
- FK order matters — `franchises` table must exist before `users.franchise_id` FK.

## Packages Installed
- `spatie/laravel-permission` — roles & permissions
- `laravel/sanctum` — API tokens
- `barryvdh/laravel-dompdf` — PDF generation
- `simplesoftwareio/simple-qrcode` — QR codes for certificates
- `maatwebsite/excel` — Excel exports

## Figma Access
- File: **Apex Brains** | ID: `3PzTEmLL3RjRXiTioCSTLP` | Page: `0:1`
- Token stored in project memory (`project_overview.md`) — use PowerShell `Invoke-RestMethod` with header `X-Figma-Token`
- Admin screens: Section `18:7062`, frames Admin 20 (`18:7063`) → Admin 38 (`18:9408`)
- Franchise screens: Section `18:10075`
- Student screens: Section `15:1883`
- Fetch a screen: `GET https://api.figma.com/v1/files/{fileId}/nodes?ids={nodeId}`

## Git
- Branch: `main`
- Remote: `origin` → `https://github.com/niteshmorepune/apexbrains.git`
- `composer` remote → laravel/laravel (template, ignore)
- GitHub Actions deploys on push to `main`

# Apex Brains Academy — Admin Portal Walkthrough

**Audience:** Internal team (client demo)
**Date:** 29 May 2026 (demo: next day)
**Portal:** Admin (Super Admin)
**URL:** `/admin/login`

---

## 1. Before the Demo — Checklist

| Item | Action |
|---|---|
| Server running | `php artisan serve --port=8000` (or live Hostinger URL) |
| Database seeded | Run `php artisan migrate:fresh --seed` so demo data + charts show |
| Browser cache | Hard-refresh (Ctrl+Shift+R) to avoid 419 / stale-asset issues |
| Login ready | `admin@apexbrains.in` / `password` (Super Admin) |
| Logo uploaded | Confirm the academy logo shows on the login screen + header |
| Tabs open | Keep the demo flow tabs pre-loaded for a smooth screen-share |

**Talking point:** Apex Brains is a **multi-tenant abacus coaching platform** with 4 portals — Admin, Franchise, Student, and External (competition-only). Today we're showing the **Admin (head-office) portal**, which controls the entire network.

---

## 2. Login

- Go to `/admin/login`.
- Show the branded login screen (academy logo, dark-navy admin theme `#1A2332`).
- Log in as **admin@apexbrains.in**.
- **Talking point:** Login is rate-limited (6 attempts/min) for security. Each portal has its own login and theme color — Admin is dark navy, Franchise blue, Student green.

---

## 3. Demo Flow — Screen by Screen

Walk the client through the sidebar **top to bottom**. For each screen, show the data, then call out one or two highlights.

### 3.1 Dashboard (`/admin`)
The landing screen — a global view of the whole network.
- **4 KPI cards:** Total Students, Active Franchises (with pending-approval count), Monthly Revenue (₹), Global Average Score.
- **Charts:** Monthly Revenue Trend (line), Students by Level (donut), Branch Performance (bar).
- **Franchise Overview table:** every branch with students, revenue, avg score, status, and quick View/Edit links.
- **Export CSV** button (top right) for the full dashboard snapshot.
- **Talking point:** "One glance tells head office how the entire network is performing this month."

### 3.2 Franchises (`/admin/franchises`)
Manage every branch in the network.
- List of all franchises with status badges.
- **Approval Queue** — new franchise applications waiting for head-office approval (Approve / Reject / Suspend).
- **Performance** view — compare branches side by side.
- Create / Edit / View a franchise. Approving a franchise **auto-creates its login account**.
- **Talking point:** "Head office onboards a new center here; the moment it's approved, the franchise owner can log in to their own portal."

### 3.3 Question Bank (`/admin/questions`)
Central repository of all exam/practice questions.
- Browse, create, edit questions; Approve / Reject workflow for quality control.
- **Audio Question Generator** (`/admin/questions/audio/generate`) — generates spoken-number audio questions for abacus listening practice. **Admin-only** feature.
- **PDF Upload & OCR** (`/admin/pdf-uploads`) — upload question PDFs and extract questions automatically.
- **Talking point:** "Questions are created once at head office and flow down to every franchise and student — consistent curriculum across the network. The audio generator is unique to head office."

### 3.4 Curriculum / Levels (`/admin/levels`)
- Define the abacus levels (the learning ladder) and what each contains.
- **Talking point:** "This is the master curriculum every student progresses through."

### 3.5 Resources (`/admin/resources`)
- Upload and manage downloadable resource files (worksheets, guides) shared across the network.
- Download / delete files.

### 3.6 Leaderboard (`/admin/leaderboard`)
- Top-performing students across all franchises.
- **Talking point:** "Drives healthy competition network-wide."

### 3.7 Competitions (`/admin/competitions`) & Practice Papers (`/admin/competition-papers`)
- Create and manage competitions and the practice papers tied to them.
- This is what feeds the **External (competition-only) portal**.

### 3.8 Settings (`/admin/settings`)
- Global platform settings — branding/logo, academy details, etc.
- **Talking point:** "The logo you saw on the login screen is set right here."

### 3.9 Finance / Revenue (`/admin/revenue`)
- Network revenue reporting with **PDF export**.
- **Commissions** (`/admin/commissions`) — calculate franchise commissions, mark as paid, export to PDF.
- **Talking point:** "Head office sees consolidated revenue and settles franchise commissions from one place."

### 3.10 Audit Log (`/admin/audit-log`) — top bar
- Immutable record of every significant action (who did what, when).
- Exportable.
- **Talking point:** "Full accountability — the audit log can't be edited or deleted, only appended."

### 3.11 Help & Guide (`/admin/help`) — top bar
- Built-in guide covering all 4 portals.

### 3.12 Profile (`/admin/profile`) — top bar
- Admin's own account details and password.

---

## 4. Suggested Demo Order (≈10 min)

1. **Login** → land on Dashboard (the "wow" overview).
2. **Dashboard** — KPIs + charts + franchise table.
3. **Franchises** → Approval Queue (show onboarding a branch).
4. **Question Bank** → Audio Generator (the standout feature).
5. **Competitions** → tie to the External portal story.
6. **Finance / Commissions** — the money story.
7. **Audit Log** — close on security & accountability.

---

## 5. Key Messages to Land

- **One platform, four roles** — Admin controls the network; franchises, students, and competitors each get a tailored portal.
- **Centralized curriculum** — questions, levels, and resources are managed once and shared everywhere.
- **Built-in revenue & commission tracking** — no spreadsheets.
- **Security first** — rate-limited logins, immutable audit trail, role-based access, exam-integrity tracking.
- **Multi-tenant by design** — each franchise sees only its own data automatically.

---

## 6. Q&A — Likely Client Questions

| Question | Answer |
|---|---|
| Can a franchise see other franchises' data? | No — data is auto-filtered per tenant. They only see their own. |
| Where do students take exams? | In the Student portal; integrity data (IP, device, tab-switches) is logged per attempt. |
| Can we change branding? | Yes — logo and academy details in **Settings**, reflected across all portals. |
| Is there attendance tracking? | Not in scope — the platform focuses on curriculum, exams, competitions, and finance. |
| How are franchises added? | Application → Approval Queue → approve (auto-creates their login). |
| Can we export reports? | Yes — Dashboard CSV, Revenue PDF, Commissions PDF, Audit Log export. |

---

*Tech: Laravel 12 · PHP 8.3 · MySQL 8.4 · Tailwind v4 · Alpine.js. Deployed on Hostinger via GitHub Actions.*

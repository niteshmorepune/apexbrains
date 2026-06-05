# Apex Brains Academy — Franchise Portal Walkthrough

**Audience:** Internal team (client demo)
**Portal:** Franchise (Branch / Franchise Admin)
**URL:** `/franchise/login`
**Theme:** Franchise blue `#1A73E8`

---

## 1. Before the Demo — Checklist

| Item | Action |
|---|---|
| Live URL ready | `https://apex.talktonitesh.com/franchise/login` |
| Demo data seeded | On the server: `php artisan db:seed --class=FranchiseWalkthroughSeeder --force` (idempotent — safe to re-run) |
| Browser cache | Hard-refresh (Ctrl+Shift+R) to avoid stale-asset / 419 issues |
| Login ready | `kothrud@apexbrains.in` / `password` (Franchise Admin — Kothrud branch) |
| Logo uploaded | Confirm the academy logo shows on the login screen, header, and receipts |
| Tabs pre-loaded | Keep the demo flow screens open for a smooth screen-share |

**Talking point:** Apex Brains is a **multi-tenant abacus coaching platform** with four portals — Admin (head office), **Franchise (branch)**, Student, and External (competition-only). Today we are showing the **Franchise portal**: what a branch owner uses day-to-day to run their center. A franchise only ever sees **its own** students and data.

---

## 2. Login

- Go to `/franchise/login`.
- Show the branded login screen (academy logo, franchise **blue** theme).
- Log in as **kothrud@apexbrains.in**.
- **Talking point:** Each portal has its own login, theme colour, and access rules. Login is rate-limited (6 attempts/min). A franchise account is **created automatically by head office** the moment the branch is approved in the Admin portal.

---

## 3. What the Franchise Owns vs. What Head Office Controls

Set this expectation up front — it explains why some things are view-only.

| Area | Who manages it |
|---|---|
| **Students** (admissions, profiles, levels) | **Franchise** |
| **Fees & Payments** (record, receipts, reminders) | **Franchise** |
| **Class Practice** (run audio/display sessions) | **Franchise** |
| **Competitions — student registrations** | **Franchise** |
| **Certificates, Promotions, Reports, Notifications** | **Franchise** |
| **Question Bank, Practice Papers, Competition Papers** | **Head Office (Admin)** |
| **Exams** (the papers themselves) | **Head Office (Admin)** — franchise **monitors** results only |

**Talking point:** "Content is authored once at head office for consistency across the whole network; the franchise focuses on running the center — admissions, fees, daily practice, and tracking student progress."

---

## 4. Demo Flow — Screen by Screen

Walk the client down the sidebar **top to bottom**. For each screen, show the data, then call out one or two highlights.

### 4.1 Dashboard (`/franchise`)
The branch landing screen — a snapshot of this center.
- KPI cards (students, fees collected, outstanding, performance) and quick activity.
- **Talking point:** "Everything here is scoped to this one branch — the owner never sees another franchise's data."

### 4.2 Students (`/franchise/students`)
The heart of the franchise — admissions and student management.
- List with search and Internal/External filters; each row links to a full student profile.
- **Add Student** — full admission form (student details, level, parent/guardian, login credentials). Internal vs. External student types.
- **Bulk Import** — onboard many students at once from a CSV/Excel template.
- Open a student → tabs for profile, parents, **Class Practice** history, etc.
- **Talking point:** "Admissions happen here. Registering a student also creates their parent record and the student's own login."

### 4.3 Fees (`/franchise/fees`)
Monthly fee collection and tracking.
- KPI strip: Collected, Outstanding, Overdue, Collection Rate (vs. previous month).
- Tabs: **All / Paid / Pending / Partial / Overdue**; filter by month and student type.
- **Record Payment** (`/franchise/fees/record`) — pick a student, choose which fee to pay, auto-fills the outstanding amount, select mode (Cash / UPI / Card / Cheque / Bank Transfer), then **Record and Generate Receipt**.
- The **Receipt** shows the academy logo, a unique receipt number, amount in words, and a **verification QR**. Buttons: **Download PDF**, **Share WhatsApp**, **Print Receipt** (prints just the receipt).
- **Reminders** (`/franchise/fees/reminders`) — outstanding fees prioritised by how overdue they are; one-click **WhatsApp** reminder to the parent (opens WhatsApp with a pre-filled message).
- **Talking point:** "From admission to a GST-style printable/PDF receipt with a QR code, the whole fee cycle is in one place. Reminders go to parents over WhatsApp."

### 4.4 Exams (`/franchise/exams`) — view only
- A read-only list of exams scheduled by head office, with **monitoring** of this branch's own students' attempts (pass counts, average score, recent attempts).
- **Talking point:** "Exam papers are set centrally for fairness; the franchise watches how its students perform but can't alter the paper."

### 4.5 Class Practice (`/franchise/class-practice`)
The franchise's signature in-class tool — an audio/display practice session for the whole class.
- **Sessions** tab — past and current practice sessions with results.
- **New session** — pick a level, number of questions, time per question, and audio dictation; the system pulls questions and launches an in-class **flashcard player** (term-by-term flash-anzan) for the projector. Students write answers in their workbook; the teacher reveals the answer at the end.
- **Practice Papers** tab — ready-made, **level-wise practice papers authored by head office**; **Attempt** launches the same flashcard player, and **Download Answer PDF** gives the answer key.
- **Talking point:** "This is the daily classroom experience — the teacher projects questions, the class solves on their abacus, and the answer key is one click away. The papers come straight from head office, so every branch teaches the same material."

### 4.6 Competitions (`/franchise/competitions`)
- Competitions announced by head office; the franchise **registers its students** to participate.
- **Talking point:** "Head office runs the competition; the branch enrolls its students from here."

### 4.7 Certificates (`/franchise/certificates`)
- Generate and manage student certificates (level completion, merit, etc.) with a live preview, level badge, and a verification QR; download as a branded single-page PDF; mark as sent.
- **Talking point:** "Branded, verifiable certificates — each one carries a QR a parent can scan to confirm it's genuine."

### 4.8 Promotions (`/franchise/promotions`)
- Students who **passed their level exam** appear as eligible; promote them to the next level in one click (their progress history is recorded).
- **Talking point:** "When a student clears their level, the owner promotes them here and the learning ladder moves up."

### 4.9 Reports (`/franchise/reports`)
- Branch and per-student progress reports; export to Excel, and download a per-student PDF.
- **Talking point:** "Shareable progress reports for parent meetings and the owner's own review."

### 4.10 Parent Directory (`/franchise/parents`)
- A consolidated directory of all parents/guardians for the branch with their contact details.

### 4.11 Notifications (`/franchise/notifications`)
- Compose a message to **All Students / by Level / an Individual**, using ready-made **templates** (Exam Result, Fee Reminder, Achievement, etc.). The message preview and recipient count update live; every message is recorded in the student's notification history.
- **Talking point:** "Quick, templated parent communication." *(See limitations below — automatic WhatsApp/SMS delivery is the next phase; today messages are logged to each student's history.)*

### 4.12 Help Guide & Profile
- **Help Guide** — in-portal reference for the owner.
- **My Profile** — the franchise's own details and password.

---

## 5. Highlights to Emphasise

- **Strict data isolation** — a franchise only ever sees its own students, fees, and reports (multi-tenant by design).
- **End-to-end fee cycle** — admission → record payment → branded PDF receipt with verification QR → WhatsApp reminders.
- **Consistent curriculum** — Question Bank, Practice Papers, Competition Papers, and Exams are authored by head office, so every branch teaches and tests identically.
- **Classroom-ready Class Practice** — projector flashcard player with head-office practice papers and instant answer keys.
- **Verifiable certificates** — branded, QR-verifiable, one-click PDF.

---

## 6. Known Limitations (be upfront)

| Item | Status |
|---|---|
| Notification Center "Send WhatsApp/SMS" (bulk) | **Logged, not yet delivered.** Messages are recorded in each student's history; automatic gateway delivery (WhatsApp Business / SMS) is the next phase. |
| Fee reminder & receipt **WhatsApp** buttons | **Working** — these open WhatsApp with a pre-filled message (deep link). |
| External-student competition exam-taking | Uses the practice flow for now (external students are competition-only, no level) — a planned follow-up. |

---

## 7. Demo Data Reference (what's seeded)

The `FranchiseWalkthroughSeeder` populates the **Kothrud** branch with identifiable demo records:

| Data | Detail |
|---|---|
| Students | 5 demo students (`KTD-DEMO-*`) + their parents |
| Fees | Current-month fees — a mix of **paid / pending / overdue** |
| Payment | One recorded payment, receipt **`KTD-2026-DEMO1`** (open it to show the PDF + print) |
| Certificate | One issued certificate (`KTD-CERT-DEMO-1`) |
| Class Practice | One completed session (`KTDEMO`) with results |
| Exam | One demo exam + a **passed** attempt → drives the Promotions screen |

*Re-running the seeder is safe (idempotent). Demo records are prefixed/identifiable so they can be cleaned up after the demo if needed.*

---

## 8. Next Step

Once the client approves the Franchise portal, we move on to the **Student Portal** walkthrough.

*Apex Brains Academy — Franchise Portal reference for the internal demo team.*

# Apex Brains Academy — Client Questions & Clarifications

**For:** Client meeting — Admin & Franchise portals
**Purpose:** Resolve open decisions, gather integration details, and confirm business rules before we proceed to the Student portal.
**How to use:** Lead with Section A (decisions that change the build) and Section B (details only the client can provide); run Section C verbally as quick confirmations.

---

## A. Decisions That Change What We Build

These change scope or behaviour — we need answers to proceed.

| # | Question | Why it matters | Client answer |
|---|---|---|---|
| 1 | **Commission basis** — calculate franchise commission on **expected revenue** (students × fee) or **only on collected payments**? | Drives Admin → Commissions & Revenue figures. We currently use an expected-revenue fallback; "collected only" means we revert it. | |
| 2 | **Online payment gateway for parents?** Today the franchise **manually records** payments (cash / UPI / cheque). Do parents need to **pay online** with auto-reconciliation? | Manual recording vs. a live gateway (e.g. Razorpay / PayU) is a major scope difference. | |
| 3 | **Bulk WhatsApp / SMS delivery** — do they want **real delivery**? Today messages are **logged, not sent**. If yes, which provider (WhatsApp Business API, MSG91, Twilio…)? | Notification Center & fee reminders. Needs a paid gateway + credentials. | |
| 4 | **Promotion criteria** — is **passing the level exam** the only condition to promote a student, or also teacher approval / minimum duration? Confirm **no attendance tracking** is needed. | Drives the Promotions flow; attendance is intentionally out of scope — worth validating. | |
| 5 | **Sub-users per branch** — does a franchise need **multiple logins with different access** (e.g. receptionist = fees only, teacher = Class Practice only)? Today it is **one franchise-admin login per branch**. | Roles & permissions scope. | |

---

## B. Integrations & Details We Need From the Client

Things only the client can provide.

| # | Item | Needed for | Client answer |
|---|---|---|---|
| 6 | **Legal / tax details** — registered entity name, **GSTIN**, address, and whether receipts must show a **GST / tax breakup**. (Receipts currently show "ISO 9001:2015," no tax line.) | Fee receipts & certificates (client-facing / financial). | |
| 7 | **Transactional email (SMTP)** — provider + credentials. **Password-reset email is currently blocked** without this. | Self-service password reset, email notifications. | |
| 8 | **Certificate signatory** — name / designation + a **signature image**; confirm the **final high-res logo** is uploaded. | Certificate & receipt branding. | |
| 9 | **Fee structure** — per-level **monthly fee amounts**, billing cycle / due date, and policy on **late fees / penalties** and **partial payments**. | Fees module accuracy (we support partial / overdue but need real numbers & policy). | |

---

## C. Business-Rule Confirmations (Quick Yes / No)

Fast confirmations — these match how the system is currently built.

| Topic | To confirm | Answer |
|---|---|---|
| **Content ownership** | Question Bank, Practice Papers, Competition Papers & Exams are **head-office authored**; the franchise **monitors exam results only** (no branch-level exam creation). | |
| **Data isolation** | A franchise must **never** see another branch's students, fees, or reports. | |
| **Curriculum** | Confirm the **number of levels** (we use up to 14) and their names. | |
| **Class Practice audio** | Is **browser text-to-speech** acceptable for audio dictation, or do they want **recorded audio / a specific voice or language**? | |
| **Competitions** | How do **external (competition-only) students** compete — a **timed online exam** in the portal, or **offline** with results entered later? And **who collects the competition fee**? | |

*Note: external-student online competition exam-taking is currently a planned follow-up — the answer to the Competitions row decides whether we build it.*

---

## Priority for the Meeting

1. **Section A** — unblocks the most work (scope-changing decisions).
2. **Section B** — gather credentials & details only the client has.
3. **Section C** — quick verbal confirmations.

*Apex Brains Academy — internal reference for the client meeting (Admin & Franchise portals).*

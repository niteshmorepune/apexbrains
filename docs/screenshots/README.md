# Responsive QA Screenshots — 2026-05-31

Captured with Playwright (Chromium) at three widths against the local dev server, logged in as the seeded demo users. Use these as the visual baseline when rechecking the responsive pass + bugfixes.

- **desktop** = 1440×900
- **tablet** = 768×1024
- **mobile** = 390×844 (iPhone-ish)

> Note: local DB has no exam/payment data, so some screens show empty states ("No exam data yet", "No revenue recorded yet"). That's data, not a layout bug.

## Admin (`admin@apexbrains.in`)
| Screen | desktop | tablet | mobile |
|---|---|---|---|
| Dashboard | admin-dashboard-desktop.png | admin-dashboard-tablet.png | admin-dashboard-mobile.png |
| Leaderboard (A10) | admin-leaderboard-desktop.png | admin-leaderboard-tablet.png | admin-leaderboard-mobile.png |
| Franchise Performance (A34) | admin-performance-desktop.png | admin-performance-tablet.png | admin-performance-mobile.png |
| Resource Library (A47) | admin-resources-desktop.png | admin-resources-tablet.png | admin-resources-mobile.png |
| Commission Calculator (A69/A71) | admin-commissions-desktop.png | admin-commissions-tablet.png | admin-commissions-mobile.png |
| Mobile nav drawer (open) | — | — | admin-drawer-mobile.png |

## Franchise (`kothrud@apexbrains.in`)
| Screen | desktop | tablet | mobile |
|---|---|---|---|
| Dashboard | franchise-dashboard-desktop.png | franchise-dashboard-tablet.png | franchise-dashboard-mobile.png |
| Students | franchise-students-desktop.png | franchise-students-tablet.png | franchise-students-mobile.png |
| Fees | franchise-fees-desktop.png | franchise-fees-tablet.png | franchise-fees-mobile.png |
| Mobile nav drawer (open) | — | — | franchise-drawer-mobile.png |

## Student (`arjun@student.in`) — mobile-first, framed column on desktop
| Screen | desktop | tablet | mobile |
|---|---|---|---|
| Home | student-home-desktop.png | student-home-tablet.png | student-home-mobile.png |

## External (`external@test.in`) — mobile-first, framed column on desktop
| Screen | desktop | tablet | mobile |
|---|---|---|---|
| Home | external-home-desktop.png | external-home-tablet.png | external-home-mobile.png |

---
Regenerate with: `php artisan serve` then `node docs/screenshots.mjs` (logs in once per portal to stay under the `throttle:6,1` login limit).

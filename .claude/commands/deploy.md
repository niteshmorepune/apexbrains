Commit all staged changes and push to GitHub to trigger the Hostinger deployment.

Steps:
1. Run git status to review what will be committed
2. Stage any unstaged changes for tracked files
3. Create a commit with a descriptive message
4. Push to origin main
5. The GitHub Actions workflow at .github/workflows/deploy.yml will auto-deploy to Hostinger
6. Confirm the run goes green (see monitoring below) before reporting success

Remember: .env is in .gitignore and must be configured separately on the server.

Monitoring the deploy (the `gh` CLI is NOT installed — use the public REST API):
- Latest run: `GET https://api.github.com/repos/niteshmorepune/apexbrains/actions/runs?branch=main&per_page=1` → `workflow_runs[0].id` / `.status` / `.conclusion` / `.head_sha`.
- Poll `runs/{id}` until `status=completed`, then read `conclusion` (`success`/`failure`). A run typically takes ~2–3 min (PHP + Node + SSH).
- Step **logs** require auth (403 unauthenticated); if a run fails, ask the user to paste the failing step's red lines.

If a deploy fails at the "Deploy to Hostinger via SSH" step: the server git update now uses `git fetch + git reset --hard origin/main` (hardened 2026-06-02), so the old flaky `git pull` aborts should be gone. A genuine failure there is more likely `migrate --force` or a cache command — get the real error before guessing. See `memory/project_deploy.md`.

**Known transient flake (seen repeatedly 2026-06-03):** the "Deploy to Hostinger via SSH" step intermittently fails because Hostinger's SSH endpoint is briefly unreachable. Tell-tale sign: the build (PHP + Node) passes and only this step fails, AND the run took ~5 min (the SSH client hung/timed out) rather than failing fast. Diagnosis: check the failing step name via `runs/{id}/jobs` (step names/conclusions need no auth) — if it's step 10 "Deploy to Hostinger via SSH" with a long runtime, it's almost certainly the flake, not your code. Fix: **re-run the failed job** in the GitHub UI (succeeds on re-run, updating the SAME run id to `success`), or push an empty commit (`git commit --allow-empty -m 'ci: re-trigger deploy'`). Don't keep auto-retrying more than once or debugging code. If it fails fast (well under the normal ~2-3 min) at a build step, that's a real error — get the logs.

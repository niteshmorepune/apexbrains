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
- Poll `runs/{id}` until `status=completed`, then read `conclusion` (`success`/`failure`). A healthy run takes ~2–3 min (PHP + Node + SSH); a flake-failing run now burns ~10 min (8 SSH retries) before failing.
- Step **logs** require auth (403 unauthenticated); if a run fails, ask the user to paste the failing step's red lines.

If a deploy fails at the "Deploy to Hostinger via SSH" step: the server git update now uses `git fetch + git reset --hard origin/main` (hardened 2026-06-02), so the old flaky `git pull` aborts should be gone. A genuine failure there is more likely `migrate --force` or a cache command — get the real error before guessing. See `memory/project_deploy.md`.

**Known transient flake (seen repeatedly 2026-06-03 and -04):** the "Deploy to Hostinger via SSH" step intermittently fails because Hostinger's SSH endpoint is unreachable from the GitHub runner (exit code **255** = SSH connect failure; it's the runner→host network path, NOT a host-wide outage — the hPanel terminal still reaches the box fine at the same time). Tell-tale sign: the build (PHP + Node) passes and only step 10 fails. As of 2026-06-04 the step retries SSH **8×** (`nick-fields/retry`, ConnectTimeout 30s, wait 45s, 25-min budget), so a recoverable blip now self-heals and a genuinely-failing run burns **~10 min** before giving up (was ~5 min at 3 attempts). Diagnose via `runs/{id}/jobs` (step names/conclusions need no auth): step 10 + long runtime = the flake, not your code.

Fixes, in order of preference:
1. **Re-run the failed job** in the GitHub UI (succeeds once the runner can reach the host again; updates the SAME run id to `success`), or push an empty commit (`git commit --allow-empty -m 'ci: re-trigger deploy'`).
2. **Sustained outage (all 8 attempts fail) → deploy manually via the hPanel Browser Terminal** — this is the reliable fallback (worked 2026-06-04). Have the user paste the exact idempotent command, then confirm `HEAD is now at <sha>` matches the pushed commit:
   ```bash
   cd ~/apex.talktonitesh.com && git fetch origin main && git reset --hard origin/main && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:clear && php artisan view:cache && php artisan event:cache
   ```
   After a manual deploy the failed Actions run is stale — ignore it.

If it fails *fast* (well under the normal ~2-3 min) at a build step, that's a real error — get the logs.

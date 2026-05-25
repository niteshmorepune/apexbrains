Commit all staged changes and push to GitHub to trigger the Hostinger deployment.

Steps:
1. Run git status to review what will be committed
2. Stage any unstaged changes for tracked files
3. Create a commit with a descriptive message
4. Push to origin main
5. The GitHub Actions workflow at .github/workflows/deploy.yml will auto-deploy to Hostinger

Remember: .env is in .gitignore and must be configured separately on the server.

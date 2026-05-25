Drop all tables and re-run all migrations and seeders from scratch.

```powershell
$env:PATH = "C:\tools\php83;C:\ProgramData\ComposerSetup\bin;" + $env:PATH
Set-Location "D:\Projects\Apex Brains App"
php artisan migrate:fresh --seed
```

Demo users restored: admin@apexbrains.in, kothrud@apexbrains.in, arjun@student.in, external@test.in — all password: `password`

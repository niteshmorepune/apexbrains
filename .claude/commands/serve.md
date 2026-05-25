Set PATH and start the Laravel dev server on port 8000.

```powershell
$env:PATH = "C:\tools\php83;C:\ProgramData\ComposerSetup\bin;" + $env:PATH
Set-Location "D:\Projects\Apex Brains App"
php artisan serve --port=8000
```

The app will be available at http://127.0.0.1:8000

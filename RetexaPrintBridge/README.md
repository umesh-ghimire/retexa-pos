# RETEXA Print Bridge

Windows-only RAW ESC/POS bridge for the RETEXA POS project.

Initial printer queue: **YICHIP POS58**. The bridge uses the Windows queue name, not USB001.

## Run

```powershell
dotnet build
dotnet run
```

The bridge listens only on `127.0.0.1:9123`.

## Test health

```powershell
Invoke-RestMethod http://127.0.0.1:9123/health
```

## Test print

Create a Base64 ESC/POS payload and POST:

```powershell
$body = @{ printer='YICHIP POS58'; job_id='test-001'; copies=1; data=$b64 } | ConvertTo-Json
Invoke-RestMethod http://127.0.0.1:9123/print -Method POST -ContentType 'application/json' -Body $body
```

The physical Windows printer must be installed as **YICHIP POS58**. Windows maps that queue to USB001.

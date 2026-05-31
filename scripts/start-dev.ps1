#Requires -Version 5.1
<#
.SYNOPSIS
    Arranca ngrok, Reverb y colas para desarrollo local (Beeffresh + Wompi).

.PARAMETER SkipNgrok
    No inicia ngrok ni modifica APP_URL.

.PARAMETER SkipReverb
    No inicia php artisan reverb:start.

.PARAMETER SkipQueue
    No inicia php artisan queue:work.

.PARAMETER NgrokExe
    Ruta a ngrok.exe. Si se omite, usa $env:NGROK_EXE o rutas habituales.

.EXAMPLE
    .\scripts\start-dev.ps1
#>
[CmdletBinding()]
param(
    [switch] $SkipNgrok,
    [switch] $SkipReverb,
    [switch] $SkipQueue,
    [string] $NgrokExe = $env:NGROK_EXE
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $ProjectRoot

function Write-Step([string] $Message) {
    Write-Host "[*] $Message" -ForegroundColor Cyan
}

function Write-Ok([string] $Message) {
    Write-Host "[OK] $Message" -ForegroundColor Green
}

function Write-WarnLine([string] $Message) {
    Write-Host "[!] $Message" -ForegroundColor Yellow
}

function Resolve-PhpExe {
    if ($env:PHP_EXE -and (Test-Path $env:PHP_EXE)) {
        return $env:PHP_EXE
    }

    $laragonPhp = Get-ChildItem 'C:\laragon\bin\php\php-8.*\php.exe' -ErrorAction SilentlyContinue |
        Sort-Object FullName -Descending |
        Select-Object -First 1

    if ($laragonPhp) {
        return $laragonPhp.FullName
    }

    $fromPathCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($fromPathCmd) {
        return $fromPathCmd.Source
    }

    throw 'No se encontro php.exe. Define $env:PHP_EXE o agrega Laragon al PATH.'
}

function Resolve-NgrokExePath {
    param([string] $Override)

    if ($Override -and (Test-Path $Override)) {
        return (Resolve-Path $Override).Path
    }

    $candidates = @(
        "$env:LOCALAPPDATA\Microsoft\WinGet\Links\ngrok.exe",
        "$env:USERPROFILE\Downloads\ngrok-v3-stable-windows-amd64\ngrok.exe",
        'C:\ngrok\ngrok.exe'
    )

    foreach ($path in $candidates) {
        if (Test-Path $path) {
            return (Resolve-Path $path).Path
        }
    }

    $fromPathCmd = Get-Command ngrok -ErrorAction SilentlyContinue
    if ($fromPathCmd) {
        return $fromPathCmd.Source
    }

    throw 'No se encontro ngrok.exe. Instala ngrok o define $env:NGROK_EXE.'
}

function Test-PortListening([int] $Port) {
    $conn = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
    return $null -ne $conn
}

function Test-HttpOk([string] $Url, [int] $TimeoutSec = 15) {
    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec $TimeoutSec
        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
}

function Get-NgrokHttpsUrl {
    $api = Invoke-RestMethod -Uri 'http://127.0.0.1:4040/api/tunnels' -TimeoutSec 5
    $tunnel = $api.tunnels | Where-Object { $_.public_url -like 'https://*' } | Select-Object -First 1
    if (-not $tunnel) {
        throw 'ngrok activo pero sin tunel HTTPS.'
    }

    return ($tunnel.public_url -replace '/$', '')
}

function Set-EnvAppUrl([string] $Url) {
    $envFile = Join-Path $ProjectRoot '.env'
    if (-not (Test-Path $envFile)) {
        throw "No existe $envFile. Copia .env.example y configura la base de datos."
    }

    $lines = Get-Content $envFile
    $found = $false
    $newLines = foreach ($line in $lines) {
        if ($line -match '^APP_URL=') {
            $found = $true
            "APP_URL=$Url"
        } else {
            $line
        }
    }

    if (-not $found) {
        $newLines = @("APP_URL=$Url") + $newLines
    }

    Set-Content -Path $envFile -Value $newLines -Encoding UTF8
}

function Start-BackgroundArtisanWindow {
    param(
        [string] $PhpExe,
        [string] $Title,
        [string] $Arguments
    )

    $psCommand = @"
Set-Location '$ProjectRoot'
`$Host.UI.RawUI.WindowTitle = '$Title'
& '$PhpExe' artisan $Arguments
"@

    Start-Process -FilePath 'powershell.exe' -ArgumentList '-NoExit', '-Command', $psCommand | Out-Null
}

Write-Host ''
Write-Host 'Beeffresh - arranque de desarrollo' -ForegroundColor White
Write-Host "Proyecto: $ProjectRoot" -ForegroundColor DarkGray
Write-Host ''

$php = Resolve-PhpExe
Write-Ok "PHP: $php"

Write-Step 'Comprobando Laragon / Apache (puerto 8080)...'
if (-not (Test-PortListening 8080)) {
    throw 'Puerto 8080 sin escucha. En Laragon: Start All y comprueba http://localhost:8080'
}

if (-not (Test-HttpOk 'http://127.0.0.1:8080')) {
    throw 'Apache en 8080 pero la app no devolvio HTTP 200. Revisa Laragon y la base de datos.'
}

Write-Ok 'App web: http://localhost:8080'

$ngrokUrl = $null

if (-not $SkipNgrok) {
    Write-Step 'ngrok (tunnel HTTPS -> 8080)...'

    if (Test-PortListening 4040) {
        Write-WarnLine 'ngrok ya activo (4040). Reutilizando tunel.'
    } else {
        $ngrok = Resolve-NgrokExePath -Override $NgrokExe
        Write-Ok "ngrok: $ngrok"
        Start-Process -FilePath $ngrok -ArgumentList @('http', '8080') -WindowStyle Minimized | Out-Null

        $deadline = (Get-Date).AddSeconds(20)
        do {
            Start-Sleep -Milliseconds 500
            $ready = Test-PortListening 4040
        } while (-not $ready -and (Get-Date) -lt $deadline)

        if (-not $ready) {
            throw 'ngrok no abrio el panel local (4040) a tiempo.'
        }
    }

    $ngrokUrl = Get-NgrokHttpsUrl
    Set-EnvAppUrl -Url $ngrokUrl
    & $php artisan config:clear | Out-Null
    Write-Ok "APP_URL actualizado: $ngrokUrl"
    Write-Ok "Webhook Wompi: $ngrokUrl/webhooks/wompi"
    Write-WarnLine 'Panel ngrok: http://127.0.0.1:4040 (URL HTTPS cambia en cada sesion con plan free).'
} else {
    Write-WarnLine 'SkipNgrok: no se modifico APP_URL.'
}

if (-not $SkipReverb) {
    Write-Step 'Laravel Reverb (WebSocket, puerto 8081)...'
    if (Test-PortListening 8081) {
        Write-WarnLine 'Puerto 8081 en uso (Reverb probablemente activo).'
    } else {
        Start-BackgroundArtisanWindow -PhpExe $php -Title 'Beeffresh Reverb' -Arguments 'reverb:start'
        Start-Sleep -Seconds 2
        if (Test-PortListening 8081) {
            Write-Ok 'Reverb: ws://localhost:8081'
        } else {
            Write-WarnLine 'Reverb en ventana nueva; revisa errores en esa terminal.'
        }
    }
}

if (-not $SkipQueue) {
    Write-Step 'Cola (default, notifications, notifications-email)...'
    $queueRunning = Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" -ErrorAction SilentlyContinue |
        Where-Object { $_.CommandLine -like '*queue:work*' }

    if ($queueRunning) {
        Write-WarnLine 'Ya hay un proceso queue:work en ejecucion.'
    } else {
        $queueArgs = 'queue:work database --queue=default,notifications,notifications-email --tries=3'
        Start-BackgroundArtisanWindow -PhpExe $php -Title 'Beeffresh Queue' -Arguments $queueArgs
        Write-Ok 'queue:work iniciado en ventana nueva'
    }
}

Write-Host ''
Write-Host 'Listo.' -ForegroundColor Green
Write-Host '  Navegacion diaria : http://localhost:8080' -ForegroundColor White
if ($ngrokUrl) {
    Write-Host "  Tunel Wompi       : $ngrokUrl" -ForegroundColor White
    Write-Host "  Webhook Wompi     : $ngrokUrl/webhooks/wompi" -ForegroundColor White
}
Write-Host '  Reverb            : ws://localhost:8081' -ForegroundColor White
Write-Host ''

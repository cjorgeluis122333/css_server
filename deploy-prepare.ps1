<#
.SYNOPSIS
    Prepara un paquete de deploy limpio para subir al hosting via FTP.

.DESCRIPTION
    1. Copia solo los archivos necesarios a una carpeta "deploy/"
    2. Usa .env.production como .env
    3. Excluye archivos de desarrollo, Docker, tests, etc.

.NOTES
    Ejecutar desde la raiz del proyecto:
        cd d:\PROGRAMACION\PHP\ccv_server
        .\deploy-prepare.ps1

    Despues de ejecutar, sube la carpeta "deploy/" al hosting via FTP.
    IMPORTANTE: Sube TODO el contenido de deploy/ a la raiz del FTP
    (donde actualmente esta el index.html del hosting).
#>

$projectRoot = $PSScriptRoot
$deployDir   = Join-Path $projectRoot "deploy"

# ─── Limpiar carpeta deploy/ anterior ─────────────────────────────────────────
if (Test-Path $deployDir) {
    Write-Host "Limpiando deploy/ anterior..." -ForegroundColor Yellow
    Remove-Item $deployDir -Recurse -Force
}
New-Item -ItemType Directory -Path $deployDir | Out-Null
Write-Host "Carpeta deploy/ creada." -ForegroundColor Green

# ─── Carpetas a copiar completas ───────────────────────────────────────────────
$foldersToInclude = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage",
    "vendor"
)

foreach ($folder in $foldersToInclude) {
    $src = Join-Path $projectRoot $folder
    $dst = Join-Path $deployDir $folder
    if (Test-Path $src) {
        Write-Host "Copiando $folder/..." -ForegroundColor Cyan
        Copy-Item $src $dst -Recurse -Force
    }
}

# ─── Archivos raiz a copiar ────────────────────────────────────────────────────
$filesToInclude = @(
    ".htaccess",
    "artisan",
    "composer.json",
    "composer.lock"
)

foreach ($file in $filesToInclude) {
    $src = Join-Path $projectRoot $file
    if (Test-Path $src) {
        Copy-Item $src $deployDir -Force
        Write-Host "Copiado: $file" -ForegroundColor Cyan
    }
}

# ─── .env.production -> .env ───────────────────────────────────────────────────
$envProd = Join-Path $projectRoot ".env.production"
$envDst  = Join-Path $deployDir ".env"
if (Test-Path $envProd) {
    Copy-Item $envProd $envDst -Force
    Write-Host "Copiado: .env.production -> .env" -ForegroundColor Cyan
} else {
    Write-Host "ADVERTENCIA: .env.production no encontrado!" -ForegroundColor Red
}

# ─── Copiar .htaccess de seguridad en carpetas sensibles ──────────────────────
$securityDirs = @("app", "config", "database", "routes", "vendor")
$htaccessContent = "Order allow,deny`nDeny from all"
foreach ($dir in $securityDirs) {
    $htaccessPath = Join-Path $deployDir "$dir\.htaccess"
    # Solo crear si no fue copiado ya del proyecto
    if (-not (Test-Path $htaccessPath)) {
        Set-Content -Path $htaccessPath -Value $htaccessContent
        Write-Host "Creado .htaccess de seguridad en $dir/" -ForegroundColor Yellow
    }
}

# ─── Limpiar archivos innecesarios dentro de las carpetas copiadas ─────────────
# Eliminar logs de storage
$logsDir = Join-Path $deployDir "storage\logs"
if (Test-Path $logsDir) {
    Get-ChildItem -Path $logsDir -Filter "*.log" | Remove-Item -Force
    Write-Host "Logs limpiados de storage/logs/" -ForegroundColor Yellow
}

# Eliminar archivos de cache de bootstrap/cache (se regeneran solos)
$cacheDir = Join-Path $deployDir "bootstrap\cache"
if (Test-Path $cacheDir) {
    Get-ChildItem -Path $cacheDir -Filter "*.php" | Remove-Item -Force
    Write-Host "Cache de bootstrap/cache/ limpiado." -ForegroundColor Yellow
}

# Eliminar archivos de cache de storage/framework/
$frameworkCacheDirs = @(
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views"
)
foreach ($dir in $frameworkCacheDirs) {
    $fullPath = Join-Path $deployDir $dir
    if (Test-Path $fullPath) {
        Get-ChildItem -Path $fullPath -File | Remove-Item -Force
    }
}

# ─── Crear archivos .gitkeep en carpetas de storage que deben existir vacias ──
$keepDirs = @(
    "storage\logs",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\app\public"
)
foreach ($dir in $keepDirs) {
    $fullPath = Join-Path $deployDir $dir
    if (-not (Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
    }
    $keepFile = Join-Path $fullPath ".gitkeep"
    if (-not (Test-Path $keepFile)) {
        New-Item -ItemType File -Path $keepFile -Force | Out-Null
    }
}

# ─── Resumen ───────────────────────────────────────────────────────────────────
$deploySize = (Get-ChildItem -Path $deployDir -Recurse -File | Measure-Object -Property Length -Sum).Sum / 1MB
Write-Host ""
Write-Host "=====================================================" -ForegroundColor Green
Write-Host " DEPLOY LISTO en: deploy/" -ForegroundColor Green
Write-Host " Tamano total: $([math]::Round($deploySize, 1)) MB" -ForegroundColor Green
Write-Host "=====================================================" -ForegroundColor Green
Write-Host ""
Write-Host "PROXIMOS PASOS:" -ForegroundColor Yellow
Write-Host "1. Edita deploy/.env y actualiza DB_PASSWORD con la nueva password de TiDB"
Write-Host "2. Conecta FileZilla a ftp.asocenca.com.ve:21"
Write-Host "3. Sube TODO el contenido de deploy/ a la raiz FTP del hosting"
Write-Host "4. Borra el index.html existente del hosting"
Write-Host "5. Abre en el navegador: https://cubano.com.ve/phpinfo.php"
Write-Host "   - Verifica PHP >= 8.2 y extensiones: pdo_mysql, mbstring, openssl, zip, bcmath, gd"
Write-Host "6. Si PHP OK, abre: https://cubano.com.ve/api/deploy-check/6b50e383c274a3fa4431be310d43993fc8e871d1b75fb9be"
Write-Host "   - Verifica que database: connected"
Write-Host "   - Verifica que todos los writable paths sean OK"
Write-Host "7. Prueba la API: https://cubano.com.ve/api/up (debe retornar 200)"
Write-Host "8. Elimina phpinfo.php del hosting via FTP"
Write-Host ""
Write-Host "SEGURIDAD POST-DEPLOY:" -ForegroundColor Red
Write-Host "- Cambia la password de TiDB Cloud (fue expuesta en el chat)"
Write-Host "- Genera un nuevo APP_KEY con: php artisan key:generate --show"
Write-Host "  y actualiza deploy/.env -> sube .env de nuevo al FTP"
Write-Host "- Cuando todo funcione: elimina la ruta /api/deploy-check de routes/api.php"
Write-Host ""

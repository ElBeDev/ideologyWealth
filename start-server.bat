@echo off
REM Ideology Wealth Advisors - PWA Local Server Starter (Windows)
REM Este script inicia un servidor local para probar la PWA

echo.
echo 🚀 Iniciando servidor local para Ideology Wealth Advisors PWA...
echo.

SET PORT=8000
if not "%1"=="" SET PORT=%1

REM Intentar con Python 3
where python >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ Usando Python...
    echo 📡 Servidor corriendo en: http://localhost:%PORT%
    echo 🔍 PWA Checker: http://localhost:%PORT%/pwa-checker.html
    echo 🎨 Icon Generator: http://localhost:%PORT%/generate-icons.html
    echo.
    echo Presiona Ctrl+C para detener el servidor
    echo.
    
    start http://localhost:%PORT%
    python -m http.server %PORT%
    goto :end
)

REM Intentar con PHP
where php >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ Usando PHP...
    echo 📡 Servidor corriendo en: http://localhost:%PORT%
    echo 🔍 PWA Checker: http://localhost:%PORT%/pwa-checker.html
    echo 🎨 Icon Generator: http://localhost:%PORT%/generate-icons.html
    echo.
    echo Presiona Ctrl+C para detener el servidor
    echo.
    
    start http://localhost:%PORT%
    php -S localhost:%PORT%
    goto :end
)

REM Intentar con Node.js http-server
where http-server >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ Usando Node.js http-server...
    echo 📡 Servidor corriendo en: http://localhost:%PORT%
    echo 🔍 PWA Checker: http://localhost:%PORT%/pwa-checker.html
    echo 🎨 Icon Generator: http://localhost:%PORT%/generate-icons.html
    echo.
    echo Presiona Ctrl+C para detener el servidor
    echo.
    
    start http://localhost:%PORT%
    http-server -p %PORT%
    goto :end
)

echo ❌ No se encontró ningún servidor disponible.
echo.
echo Por favor, instala una de estas opciones:
echo   - Python 3: https://www.python.org/downloads/
echo   - Node.js http-server: npm install -g http-server
echo   - PHP: https://www.php.net/downloads
echo.
echo O usa la extensión 'Live Server' en VS Code
pause

:end

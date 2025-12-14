@echo off
echo ========================================
echo   Iniciando Proyecto Modas Boom
echo ========================================
echo.

REM Agregar PHP y Composer al PATH
set PATH=C:\xampp\php;C:\ProgramData\ComposerSetup\bin;%PATH%

REM Agregar Node.js al PATH
set PATH=C:\Program Files\nodejs;%PATH%

echo [1/2] Verificando dependencias de PHP...
composer install --no-interaction
echo.

echo [2/2] Verificando dependencias de Node.js...
npm install
echo.

echo ========================================
echo   Dependencias instaladas correctamente
echo ========================================
echo.
echo Para iniciar el servidor Laravel:
echo   php artisan serve
echo.
echo Para iniciar Vite (en otra terminal):
echo   npm run dev
echo.
pause

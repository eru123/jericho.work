@REM set the root directory
set ROOT_DIR=%~dp0

@REM set the current directory
set CURRENT_DIR=%cd%

@REM Make sure that git is updated
git fetch
git pull

@REM Install/build CDN client
cd %ROOT_DIR%html\client\cdn
pnpm install
pnpm run build

@REM Install/build Admin client
cd %ROOT_DIR%html\client\admin
pnpm install
pnpm run build

@REM Install/build Main client
cd %ROOT_DIR%html\client\main
pnpm install
pnpm run build

@REM Install the composer dependencies and cd back to the original directory
composer install -d %ROOT_DIR%html --no-interaction -o && cd %CURRENT_DIR%
@REM set the root directory
set ROOT_DIR=%~dp0

@REM set the current directory
set CURRENT_DIR=%cd%

@REM Make sure that git is updated
git fetch
git pull

@REM Update/build CDN client
cd %ROOT_DIR%html\client\cdn
pnpm update
pnpm run build

@REM Update/build Admin client
cd %ROOT_DIR%html\client\admin
pnpm update
pnpm run build

@REM Update/build Main client
cd %ROOT_DIR%html\client\main
pnpm update
pnpm run build

@REM Update the composer dependencies and cd back to the original directory
composer update -d %ROOT_DIR%/html --no-interaction -o && cd %CURRENT_DIR%

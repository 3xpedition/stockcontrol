@echo off
setlocal
cd /d "%~dp0"

set "PHP_BIN="
for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_BIN set "PHP_BIN=%%P"

if not defined PHP_BIN (
    for /d %%D in ("C:\laragon\bin\php\php-*") do if not defined PHP_BIN if exist "%%~fD\php.exe" set "PHP_BIN=%%~fD\php.exe"
)

if not defined PHP_BIN (
    echo PHP nao foi encontrado. Abra o Laragon ou adicione o PHP ao PATH.
    pause
    exit /b 1
)

if not exist "%~dp0artisan" (
    echo O arquivo artisan nao foi encontrado na pasta do projeto.
    pause
    exit /b 1
)

for /f "usebackq delims=" %%I in (`powershell -NoProfile -Command "(Get-NetIPAddress -AddressFamily IPv4 ^| Where-Object { $_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.254.*' } ^| Select-Object -First 1 -ExpandProperty IPAddress)"`) do set "LAN_IP=%%I"

echo.
echo Sistema iniciado para acesso pela rede na porta 8000.
if defined LAN_IP echo Neste computador: http://localhost:8000 ^| Outros dispositivos: http://%LAN_IP%:8000
if not defined LAN_IP echo Endereco deste computador: descubra o IPv4 com ipconfig e abra http://IP:8000
echo Mantenha esta janela aberta enquanto usar o sistema.
echo.

"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000

echo.
echo O servidor foi encerrado.
pause

fazer php.ini, descomentar:
extension=sqlite3
extension=pdo_sqlite
; On windows:
extension_dir = "C:\Program Files\php\ext"

pra iniciar o servidor: php -S localhost:8000
pra instalar as extensões do vs code: Get-Content .\extensions-list.txt | ForEach-Object { code --install-extension $_ }
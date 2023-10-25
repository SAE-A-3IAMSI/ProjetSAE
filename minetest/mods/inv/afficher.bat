@echo off
echo "Affichage des inventaires"
@REM dir C:\Users\jojod\SAE-A3\ProjetSAE\Minetest\minetest-5.7.0-win64V2\worlds\testmonde\inventory_*.json
for /r C:\Users\jojod\SAE-A3\ProjetSAE\Minetest\minetest-5.7.0-win64V2\worlds\testmonde %%i in (inventory_*.json) do (
    echo %%i
    echo "boucle"
    curl -X POST webinfo.iutmontp.univ-montp2.fr/~pierrevelcina/returnJson.php -d @%%i
)
pause

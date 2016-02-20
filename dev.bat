start browser-sync start --files="public/assets/*/*.*,ressources/views/*.*,ressources/views/*/*.*" --proxy="localhost:8888"
start gulp watch
cd public
php -S localhost:8888
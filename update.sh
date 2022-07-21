git pull
composer install

php artisan clear
php artisan cache:clear
php artisan config:clear
php artisan migrate --force
php artisan queue:restart

npm install
npm run build

kill -9 $(lsof -t -i:9999)
php artisan serve --host=192.168.50.6 --port=9999

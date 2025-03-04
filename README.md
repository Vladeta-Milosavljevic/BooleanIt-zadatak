
### Dependencies

* PHP development enviroment (like XAMPP) is required
* If you are using XAMPP please put the project inside the htdocs folder in XAMPP. 
* Node.js is required
* Laravel is required

### Installing

* Laravel install

```
composer global require laravel/installer
```

* Install the necesary files
```
composer install
```
```
npm install
```

* Install Csv League package

```
composer require league/csv:^9.21.0 
```
* Copy .env.example into .env
* Generate App Key in the .env file
```
php artisan key:generate
```

* Or run this command and copy the key from the terminal to the APP_KEY= line in the .env file
```
php artisan key:generate --show
```

* Run the following line in the terminal
  
```
php artisan serve
```


* In the separate terminal run
```
php artisan queue:work
```

* Artisan command for taking data from Csv file (path towards the file must be inside the '')
```
php artisan csv:upload {file}
```

* Artisan command for creating a Csv file with products for the specified category (id must be inside the '')
```
php artisan csv:create_product_list_for_category {id}
```

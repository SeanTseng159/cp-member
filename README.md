<p align="center">
	<img width="120px" src="https://citypass.tw/dist/img/logo.c862357.png">
	<img width="120px" src="https://laravel.com/assets/img/components/logo-laravel.svg">
</p>

# citypass 都會通 API

> [API 文件](https://docs.google.com/document/d/17p_UjZoGNmQtGf9UVJp1Jsj6cPzUox-5Sa1PUlGPE-k/edit)
> [餐車API 文件](https://docs.google.com/document/d/1Y8C2cRS-av0zEUZus7hwiIT3s7yLlUx3gw0Q7E3Xw_4/edit#heading=h.i4vxyfuzqyiz)
> [核銷API 文件](https://docs.google.com/document/d/19LZGWO_MHCichyHHXQKAgjfYXszcNcTMFdmqFuHVexg/edit#heading=h.i4vxyfuzqyiz)

## Requirements

* laravel = 5.6
* php <= 7.x.x
* mysql <= 5.7
* composer <= 1.x.x
* nodejs <= 8.x.x
* npm <= 5.x.x

## Develop Build Setup

``` bash
# clone
$ git clone https://github.com/DCViewInc/City-pass-member.git
$ cd City-pass-member

# install dependencies
$ composer install
$ npm install
$ cp .env.example .env
$ git submodule init
$ git submodule update

# run server at localhost:8000
$ php artisan serve
```

## Commands
- 建立 Model/Repository/Service

``` bash
# 一次全部建立 (default namespace)
# ex: php artisan make:mrs User
# default namespace: App\Models

$ php artisan make:mrs {TableName}

# 一次全部建立 (use subnamespace)
# ex: php artisan make:mrs User --subnamespace=Ticket
# namespace: App\Models\Ticket

$ php artisan make:mrs {TableName} --subnamespace={XXXX}

# Create Model (default namespace)
# ex: php artisan make:mymodel User

$ php artisan make:mymodel {TableName}

# Create Model (use subnamespace)
# ex: php artisan make:mymodel User --subnamespace=Ticket

$ php artisan make:mymodel {TableName} --subnamespace={XXXX}

# Create Repository (default namespace)
# ex: php artisan make:repository User

$ php artisan make:repository {TableName}

# Create Repository (use subnamespace)
# ex: php artisan make:repository User --subnamespace=Ticket

$ php artisan make:repository {TableName} --subnamespace={XXXX}

# Create Service (default namespace)
# ex: php artisan make:service User

$ php artisan make:service {TableName}

# Create Service (use subnamespace)
# ex: php artisan make:service User --subnamespace=Ticket

$ php artisan make:service {TableName} --subnamespace={XXXX}
```


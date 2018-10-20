# Council [![Build Status](https://travis-ci.org/JeffreyWay/council.svg?branch=master)](https://travis-ci.org/JeffreyWay/council)

This is a forked version of an open source forum that was built and maintained at Laracasts.com.

We have used laravel-heyman package to re-write some it's code as an example usage.


Visit: https://github.com/imanghafoori1/laravel-heyman


## Installation

### Prerequisites

* To run this project, you must have PHP 7 installed.
* You should setup a host on your web server for your local domain. For this you could also configure Laravel Homestead or Valet. 
* The css and js assets are pre-compiled and put in the public folder, hence you do not need to run install npm
* Database driver is already set to sqlite, and a seeded, ready to use version of sqlite database is provided and configured for you, hence there is no need to migrate or seed the database to get started.
(see database/database.sqlite)  
* On windows you can run the tests with running 'phpunit' command  

### Step 1

Begin by cloning this repository to your machine, and installing all Composer dependencies.

```bash
git clone git@github.com:imanghafoori1/council.git
cd council && composer install
php artisan serve

```

### Step 2

Next, boot up a server and visit your forum. If using a tool like Laravel Valet, of course the URL will default to `http://council.test`. 

1. Visit: `http://council.test/register` to register a new forum account.
2. Edit `config/council.php`, and add any email address that should be marked as an administrator.
3. Visit: `http://council.test/admin/channels` to seed your forum with one or more channels.

# Zeus Framework
A lightweight framework with an annotation-based approach for routing.

## Usage
Using annontation `@Route` you set a unique pattern. When Zeus detect this
pattern in a request, redirects to properly class/method and execute it
STATICALLY.

```php
<?php

namespace MyBlog;

class Post
{

    /** @Route("post/show/$id") */
    public static function show($id)
    {
        // code goes here
    }

    /** @Route("post/create") */
    public static function create()
    {
        // code goes here
    }

    /** @Route("post/edit/$id") */
    public static function edit($id)
    {
        // code goes here
    }

}

?>
```

## Installation
This library can be found on [Packagist](https://packagist.org/packages/natanaelsimoes/zeus-framework).
We endorse that everything will work fine if you install this through [composer](http://getcomposer.org).

Add in your `composer.json`:
```json
{
    "require": {
        "natanaelsimoes/zeus-framework": "0.1.0"
    }
}
```
or in your bash:
```bash
$ composer require natanaelsimoes/zeus-framework
```

You need to create 2 files on your project root folder: `zeus.json` containing
Zeus configuration (see more at Configuration section below), and `index.php`
just calling Zeus for the first time.

```php
<?php
include_once 'vendor/autoload.php';
Zeus\Framework::start();
?>
```

## Configuration
To configure Zeus, a `zeus.json` file needs to be created at project root
folder. Following is the configuration file with all possible parameters.

```json
{
    "database": {
        "driver": "mysql",
        "host": "localhost",
        "port": "3306",
        "dbname": "information_schema",
        "username": "root",
        "password": ""
    },
    "routes": {
        "initialDirectory": "src/",
        "index": "post"
    },
    "development": true,
    "cache": "xcache"
}
```

### Database

Database connection is provided by [Doctrine](http://www.doctrine-project.org).
Drivers supported are `pdo_mysql`, `pdo_sqlite`, `pdo_pgsql`, `pdo_oci`,
`pdo_sqlsrv`, `oci8`.

If your project will not use any database, you can remove this parameter.

### Routes
**! This parameter is MANDATORY !**

It tells the framework to look recursively inside `initialDirectory` for methods
with `@Route` annotation. When no pattern is given by user (as for homepage),
`index` informs what pattern to execute.

### Development
**! This parameter is MANDATORY !**

Sets the project to `development` mode (if true) or production mode (if false)

### Cache
If you need to use a cache system, in `cache` parameter inform which of the
following will be used by framework:
* apc (APC)
* couchbase (Couchbase)
* file (Filesystem, saved on /cache in root)
* mem (Memcached)
* mongodb (MongoDB, not implemented yet)
* phpfile (PhpFile, saved on /cache in root)
* redis (Redis)
* riak (Riak, not implemented yet)
* wincache (WinCache)
* xcache (Xcache)
* zend (ZendData)
* none (No cache is used)

Cache is made currently based on URL. Inside the class/method you want to cache,
do as follow:

```php
/** @Route("post/create") */
public static function create()
{
    Zeus\Cache::getInstance()->getCache();
    // code goes here
    Zeus\Cache::getInstance()->setCache();
}
```

Method `Zeus\Cache::getInstance()->getCache()` verifies if there is a valid
cache version of what user requested. If exists, prints and performs `exit`.
If not valid (expired ttl) or not exists, continues generating the page normaly,
then creates the cached version at `Zeus\Cache::getInstance()->setCache()`.

If your project will not use cache, you can remove this parameter.

## Testing

For testing you need to change parameter "url" in `test.json` providing HTTP
path to test/ folder
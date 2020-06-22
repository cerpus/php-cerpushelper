# Cerpus Helper

This package contains some useful and common helper utilities for use in Laravel based projects.

## Installation
Make sure you have the following in your composer.json
```$json
    ...
"repositories": [
        {
            "type": "composer",
            "url": "https://composer.cerpus.net/"
        },
        {
          "type" : "composer",
          "url" : "https://composer-3rdparty.cerpus.net/"
        }
    ]
    ...
```
To install this package run
`composer require cerpus/cerpushelper`
in your project

## Models/Traits/UuidAsId
Let you use the primary key as UUID not the default auto incrementing value

### Usage
In your model
```$php
namespace App;

use Cerpus\Helper\Models\Traits\UuidAsId;

...

class MyModel extends Authenticatable
{
    use UuidAsId;
    
    ...
```

## Middleware/RequestId
Handle RequestId. Pick RequestId from request header and if that does not exist generate a new RequestId. 

Adds the RequestId to response headers. 

Include the RequestId when logging and requests to other systems to easily trace requests through different systems.

### Installation
In the global middleware of your app  `app/Http/Kernel.php`
```php
  use Cerpus\Helper\Middleware\RequestId;
  ...
  protected $middleware = [
        RequestId::class, 
        ...
    ];
```

You can also put it in front of API endpoints and the like to fine tune better.

### Usage
To access the requestId in your app 

`$requestId = app("requestId")`

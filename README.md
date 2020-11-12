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

### Profile
Can support simple profiles in Laravel by adding subdirectories in the config folder and name it with the profile name as folder name.
Add the config files(or only parts of it) in the profile folder to override the default values. 
_The name of the files must be identical to the one you want to override._
```
|-- config
    |-- myprofile
        |-- setting.php
    |-- setting.php
```

Then import the "profile" function in the namespace area to use the logic. 
```php
use function Cerpus\Helper\Helpers\profile as config;
```
This will use the profile values, if found, instead of the default config values.

*Will look for 'app.deploymentEnvironment' in app.php if the function is not provided with a profile*

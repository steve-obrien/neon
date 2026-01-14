# Helpers

## Introduction

Neon provides many useful global helper functions.

### Framework

- [neon](#neon)

### Http
 
- [url](#url)
- [url_secure](#url_secure)

- [setting_set](#setting_set)
- [setting_get](#setting_get)

- [dd](#dd)
- [dp](#dp)

### `neon()`

The `neon` function returns the main app instance. The app instance is 
the main glue for the applications - it is essentially a service 
container for all the services provided by your application.

Returning core services.

```php
neon()->request
neon()->response
neon()->db
```

Returning apps. Neon is essentially a collection of apps.

```php
$settings = neon('settings') // returns the Settings app. \neon\settings\App
```


### `url()`

```php
url('/my/path', ['id' => 1]);
```


### `url_secure()`


### `env()`

The env function gets an environment variable or returns a default value

```php
// Returns the value of 'MY_VARIABLE' or null if it does not exist
$var = env('MY_VARIABLE'); 
// Returns 'test_db' as the default value if 'DB_NAME' doesn't exist...
$dbName = env('DB_NAME', 'test_db');
```

Bash tips:  
- You can set environment variables via: <code>export DB_NAME="my_db_name"</code>
- Unset environment variables via: <code>unset DB_NAME</code>
- Show environment variables in bash via the env command: <code>env</code>

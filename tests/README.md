# Neon Test suite.

This is a collection of tests suites that puts neon through its passes.

The test suite currently requires root database credentials so it can create databases.

you can configure this for your particular environment in two ways:

### 1: Environment variables:

setting environment variables for example:

```
export DB_USER = 'root'
export DB_PASSWORD = 'root'
```

This is useful when using other services to run the test suite.

#Test _root directory
The framework across unit, api and acceptance tests uses the _root as a test root when setting up a test web application.
The '_root' then mirrors a typical project root and contains the following directories:

- **config/** - The config path containing the env.ini file configured with the test database settings
- **public/** - a public web root
  - **assets/** - the public assets path used by the assetManager
  - **index.php** - the entry script
- **var/** - the var folder where cache and compiled information is stored - note this will be destroyed and created before the start of each test suit run
- **system/** - some core services create files in the system folder - this too will be destroyed before and after each test suite run


**Note that var will store cached files if you are experiencing bugs when running the framework multiple times - it may be due to the var directory not correctly cleaning its self up between test runs**
# Tests

<a name="introduction"></a>
## Introduction

Writing tests for neon.  Tests are generally driven by PHPUnit and codeception.


## Setup

### CLI - PHP and MySQL

Make sure php and mysql commands are pointing to the correct versions.  

- `which php`
- `which mysql`

The above commands on a mac using MAMP should show something similar to:
- `/Applications/MAMP/bin/php/php7.1.1/bin/php`
- `/Applications/MAMP/Library/bin/mysql`

### Database

The test framework needs root access to create database tables and mysql users (It creates `neon_test` database and a user called `neon`).  
Its default settings for the database environment variables are:
 
- `DB_USER = 'root'`
- `DB_PASSWORD = 'root'`

If your test machine has different database access details you can do one of the following:

- Change the `DB_USER` and `DB_PASSWORD` environment variables on the machine manually (e.g. `export DB_PASSWORD="pass"`) or via a deploy script or other method (this is useful for CI servers)
- Add an `env.ini` file in the root of tests directory (`/tests/env.ini`) setting these values - make sure it does not get checked in to git

<p class="tip tip--note">Note that environment variables set on the machine will override any variables in your env.ini. In other words the env.ini file only sets environment variables that do not already exist</p>

Once set up the test framework creates a test database called neon_test which it runs all migrations against (`neon()->install()`)
 
## Running tests

There is a `./codecept` file in the root that symlinks to `./vendor/bin/codecept` 
Therefore tests can be ran from the command line via the following commands:

 - `./codecept run` - runs the entire test suite
 - `./codecept run unit` - runs all unit tests
 - `./codecept run acceptance --steps` - runs all acceptance tests (--steps option provides a nice verbose breakdown of the test steps taken)
 
Within each suite type (`acceptance` | `unit` | `api`) group tests inside directories by their app name.
For example the neon core suites are divided like so:

```
-- tests/
   |-- acceptance
   |  |-- core
   |  |-- firefly
   |  |-- user
   |  |-- ...
   |-- unit
   |  |-- core
   |  |-- firefly
   |  |-- user
   |  |-- ...
```
You can run any group of tests nested by its directory structure.  For example:

`./codecept run unit core`

This runs all the unit tests in the core directory.
This also works for any depth of sub folder e.g.

`./codecept run unit core/helpers`

This command will run all the tests within the directory `tests/unit/core/helpers`.
By the same principle you can run an individual file by its path:

`./codecept run unit core/helpers/ArrTest`

To run an individual function you can do:

`./codecept run unit core/helpers/ArrTest:testPluck`

## Acceptance

Currently acceptance tests spin up a local php server on `http://localhost:4444` the document root for this 
test server is inside `tests/acceptance/_web`

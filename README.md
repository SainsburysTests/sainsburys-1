Sainsbury's Fruit Scraper
=========================

Dependencies
------------

This project has two dependencies:
- PHPUnit for unit testing
- Symfony/console for providing command line interface tools

These dependencies can be installed using Composer:
> php composer.phar install


Execution
---------

Run the script on the command line with:
> php sainsburys.php run

The command has an optional argument --prettyprint to return formatted JSON
> php sainsburys.php run --prettyprint

Optionally the output json can be viewed in the browser by visiting ./index.php


Testing
-------

Unit tests are provided in the /tests directory.
You can run them using:

> ./run_tests.sh

or

> vendor/bin/phpunit tests

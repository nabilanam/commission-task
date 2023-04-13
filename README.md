# Commission Task
A symfony 6 console application to calculate deposit and withdrawal commission fees. This application reads a csv file with operations and outputs the commission fees to the console. PSR-12 compliant and tested with 75+ phpunit test cases.

## Implementation Overview
- PHP 8.2 with bcmath extension
- Symfony 6 framework
- PSR-4 autoloading
- No unused dependencies
- Dependency injection instead of hard coupling
- 75 unit tests for maximum code coverage
- 1 integration test against example csv file
- PSR-12 compliant
- No external infrastructure dependencies

## Assumptions
- CSV file operations are sorted by date in ascending order
- CSV file does not contain any invalid operations
- Commission fees are rounded up to nearest decimal places
   - EUR, USD has 2 decimal places so 0.001 becomes 0.01
   - For JPY 0.1 becomes 1 and 0.01 becomes 0

## Caching
- Exchange rates are cached in filesystem for a day
- Transactions are cached in memory

## Libraries
- symfony 6 framework and it's dependencies
- phpunit
- php-cs-fixer

## Installation and Usage
This project requires PHP 8.2 or higher with bcmath extension.

Two ways to run the project:

1. Docker:
    1. Clone the project
    2. Run `docker build -t calculator .`
    3. Run `docker run --rm calculator bin/phpunit src/Tests/Unit` to run unit tests
    4. Run `docker run --rm calculator bin/phpunit src/Tests/Integration` to run test against csv file
    5. Run `docker run --rm -v path-to-csv-file:/data.csv calculator bin/console CalculateCommission /data.csv` to run the application
2. Local:
    1. Clone the project
    2. Run `composer install`
    3. Run `php bin/phpunit src/Tests/Unit` to run unit tests
    4. Run `php bin/phpunit src/Tests/Integration` to run test against csv file
    5. Run `php bin/console CalculateCommission path-to-csv-file` to run the application

## Author Information
- Name: Anamul Haque Nabil
- Email: nabil_code@outlook.com
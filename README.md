# Log Parser

## Installation

Clone project
```
cp .env.dist .env #change db connection
composer install
./bin/console doctrine:database:create
./bin/console doctrine:schema:update --force
```

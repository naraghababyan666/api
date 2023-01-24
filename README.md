
## Build Docker

docker stop $(docker ps -qa)

docker-compose up -d

docker exec -it web bash

## Install composer package

https://getcomposer.org/download/

composer install

## Create .env file
cp .env.example .env

php artisan key:generate
## Add to .env
L5_FORMAT_TO_USE_FOR_DOCS="yaml"


## Run Migrations
php artisan migrate

## Run Seeds
php artisan db:seed

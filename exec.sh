#!/bin/bash

#-------------------#
#----- Helpers -----#
#-------------------#

usage() {
    echo "$0 [COMMAND] [ARGUMENTS]"
    echo "  Commands:"
    echo "  - up: rebuild and start all containers"
    echo "  - down: stop all containers"
    echo "  - configure: configure application"
    echo "  - php: run command inside php container"
    echo "  - composer: run command for composer"
    echo "  - recreate: recreate docker containers for env refresh"
    echo "  - dbrebuild: rebuild database"
}

fn_exists() {
    type $1 2>/dev/null | grep -q 'is a function'
}

COMMAND=$1
shift
ARGUMENTS="${@}"

#--------------------#
#----- Commands -----#
#--------------------#

up() {
    docker-compose up -d --build --remove-orphans;
}

down() {
    docker-compose down;
}

recreate() {
    docker-compose up -d --force-recreate;
    docker-compose exec php php artisan config:clear;
}

cc() {
    docker-compose exec php php artisan config:clear;
}

dbrebuild() {
    docker-compose exec php php artisan migrate:fresh;
    docker-compose exec php php artisan migrate;
    docker-compose exec php php artisan db:seed;
    docker-compose exec php php artisan config:clear;
}

configure() {
    if [ ! -f .env ]; then
        cp .env.example .env;
        docker-compose exec php php artisan key:generate;
    fi;
    curl -fsSL 'https://getcomposer.org/composer.phar' -o ./composer.phar;
}

php() {
    docker-compose exec php ${@};
}

composer() {
    if [ ! -f composer.phar ]; then
        configure;
    fi;
    docker-compose exec php php composer.phar ${@};
}

test() {
    docker-compose exec php ./vendor/bin/phpunit --testdox
}
#---------------------#
#----- Execution -----#
#---------------------#

fn_exists $COMMAND
if [ $? -eq 0 ]; then
    $COMMAND "$ARGUMENTS"
else
    usage
fi

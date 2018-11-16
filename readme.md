# Todo App 
Basic todo app written in Laravel and Vue.js

### Prerequisites
* NPM - v6.4.1
* Docker - v18.06.1 (optional)

### Installation

#### Clone the repository
`git clone git@bitbucket.org:remotecraftsmen/basic-laravel-app.git`

#### Install dependencies
`composer install`

`npm install`
`npm run production`

#### docker setup
`docker-compose up --build`

#### Copy the example env file and make the required configuration changes in the .env file
`cp .env.example .env`

#### Generate a new application key
`php artisan key:generate`

#### Run the database migrations (Set the database connection in .env before migrating)
`php artisan migrate`

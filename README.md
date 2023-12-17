# SYMFONY REST API

## Description
Briefly describe what your Symfony project does.

## Prerequisites
List any prerequisites needed to install and run your project, such as:
- PHP version (e.g., PHP 8.1 or higher)
- Composer
- Any other specific server requirements or dependencies

## Installation
1. **Clone the Repository**
`git clone git@github.com:Dezorel/symfony-rest-api.git symfony-rest-api`
2. **Install Dependencies**
`composer install`
3. **Configure Environment Variables**
- Modify the `.env` file to set up your database
`DATABASE_URL="mysql://custom_user:X0h6ckk4J@J5pcO&1MAC@127.0.0.1:3306/API_DB"`
- Set up your database by mysql dump
`./config/dump/API_DB.sql`
4. **Create directory for book catalog**
`./public/catalog/`
5. **Configure Nginx and PHP_FPM for your project**
- Set up your project to run with ngixn and php-fpm 

## Update Swagger documentation
`php bin/console nelmio:apidoc:dump > public/swagger.json`
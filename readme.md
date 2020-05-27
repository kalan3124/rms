
## Installation

1. Clone this repository

    ```
    $ git clone https://github.com/kalan3124/rms.git
    ```

How to upload a project to Github from scratch
Follow these steps to project to Github

1) git init

2) git add .

3) git commit -m "Add all my files"

4) git remote add origin https://github.com/kalan3124/rms.git

Upload of project from scratch require git pull origin master.

5) git pull origin master

6) git push origin master

-------------------------------------------------------------------------------------------------
1. Install backend depedencies and front end depedencies.
    ```
    $ cd rsm
    $ composer install
    $ npm install
    ```
2. Edit configuration files.
    ```
    $ cp .env.example .env
    $ nano .env

    $ cd ./resources/js/constants
    $ cp configSample.js config.js
    $ nano config.js
    ```
3. Generate an application key and create a passport key
    ```
    $ php artisan key:generate
    $ php artisan passport:install
    ```
4. Serve the project
    ```
    $ php artisan serve
    ```


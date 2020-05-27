<p align="center"><img src="https://i.imgur.com/Lu2Kmy7.jpg"></p>

---

![Build:Passing](http://git.ceylonlinux.lk/sfa/shl-backend/badges/dev/pipeline.svg)
---
# Sunshine Healthcare Lanka FFA and SFA

This project is developed for sunshine healthcare lanka (PVT) LTD. 

- [Live System](http://shl.salespad.lk/healthcare)
- [Wiki](http://git.ceylonlinux.lk/sfa/shl-backend/wikis/home)

## Installation

1. Clone this repository

    ```
    $ git clone http://git.ceylonlinux.lk/sfa/shl-backend.git
    ```

2. Checkout to the development branch

    ```
    $ git checkout dev
    ```
3. Install php oci extension. Follow [these](https://gist.github.com/Yukibashiri/cebaeaccbe531665a5704b1b34a3498e) steps to install.
4. Install backend depedencies and front end depedencies.
    ```
    $ cd shl-backend
    $ composer install
    $ npm install
    ```
5. Edit configuration files.
    ```
    $ cp .env.example .env
    $ nano .env

    $ cd ./resources/js/constants
    $ cp configSample.js config.js
    $ nano config.js
    ```
6. Generate an application key and create a passport key
    ```
    $ php artisan key:generate
    $ php artisan passport:install
    ```
7. Serve the project
    ```
    $ php artisan serve
    ```


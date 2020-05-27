const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    output: {
        publicPath: process.env.MIX_PUBLIC_PATH || '/healthcare-test/',
        chunkFilename: 'js/[name]-'+((new Date).getTime())+'.js'
    },
});

mix.react('resources/js/app.js', 'public/js').sourceMaps(true);
mix.sass('resources/sass/app.scss', 'public/css').sourceMaps(true);;

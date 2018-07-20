var Encore = require('@symfony/webpack-encore');
var CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    // the public path you will use in Symfony's asset() function - e.g. asset('build/some_file.js')
    .setManifestKeyPrefix('build/')

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())

    // the following line enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    //.addEntry('js/app', './assets/js/app.js')
    .addStyleEntry('css/index', './assets/css/index.sass')

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()

    .addPlugin(new CopyWebpackPlugin([
        { from: './assets/upload/avatar', to: 'upload/avatar' }
    ]))

    .addPlugin(new CopyWebpackPlugin([
        { from: './assets/article/image', to: 'article/image' }
    ]))

    .addPlugin(new CopyWebpackPlugin([
        { from: './assets/activite/image', to: 'activite/image' }
    ]))
;

module.exports = Encore.getWebpackConfig();

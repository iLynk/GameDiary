const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addStyleEntry('app', './assets/styles/app.scss')

    .enableSassLoader()
    .enablePostCssLoader()  // Si vous utilisez PostCSS

    // Activer le "single runtime chunk"
    .enableSingleRuntimeChunk()

    // Autres configurations
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableVersioning(Encore.isProduction());

module.exports = Encore.getWebpackConfig();

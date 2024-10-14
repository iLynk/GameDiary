const Encore = require('@symfony/webpack-encore');

Encore
    // Répertoire où seront stockés les assets compilés
    .setOutputPath('public/build/')
    // Chemin public utilisé par le navigateur pour accéder aux assets
    .setPublicPath('/build')
    // Ajouter des fichiers d'entrée (JS ou CSS/SCSS)
    .addEntry('app', './assets/js/app.js')
    // Correction : Nom unique pour chaque fichier CSS généré
    .addStyleEntry('app_styles', './assets/styles/app.scss')
    .addStyleEntry('admin_styles', './assets/styles/admin.scss')
    .addStyleEntry('login_styles', './assets/styles/login.scss')
    .addStyleEntry('navbar_styles', './assets/styles/navbar.scss')
    .addStyleEntry('register_styles', './assets/styles/register.scss')
    .addStyleEntry('game_styles', './assets/styles/game.scss')

    // Configurer les options de surveillance pour Webpack Watcher
    .configureWatchOptions(function(watchOptions) {
        watchOptions.poll = 1000; // Re-vérifie les fichiers toutes les secondes
        watchOptions.ignored = /node_modules/; // Ignore les fichiers dans node_modules
    })

    // Active le traitement des fichiers SCSS
    .enableSassLoader()

    // Activer le chargement de PostCSS (utile pour l'autoprefixing, etc.)
    .enablePostCssLoader()

    // Activer les sources maps (utile pour le débogage)
    .enableSourceMaps(!Encore.isProduction())

    // Activer le versionnement des fichiers (cache-busting)
    .enableVersioning(Encore.isProduction())

    // Réduire le nombre d'alertes affichées par Webpack
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSingleRuntimeChunk()
;

// Exporter la configuration Webpack
module.exports = Encore.getWebpackConfig();

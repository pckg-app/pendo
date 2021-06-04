const base = require('pckg-app-frontend/full.loader.js');

module.exports = base.exports({
    entry: {
        libraries: './app/pendo/public/js/libraries.js',
        app: './app/pendo/public/js/footer.js',
    }
});

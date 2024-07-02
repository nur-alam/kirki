let mix = require('laravel-mix');

mix.setPublicPath('./assets/dist');

mix.js('integrations/src/button.js', 'assets/dist/js/button.min.js');
mix.js('integrations/src/droip-integrations.js', 'assets/dist/js/droip-integrations.min.js').react();

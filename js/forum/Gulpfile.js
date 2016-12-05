var flarum = require('flarum-gulp');

flarum({
  modules: {
    'amaurycarrade/flarum-ext-syndication': [
      'src/**/*.js'
    ]
  }
});

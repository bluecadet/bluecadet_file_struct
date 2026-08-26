
let drupal_modules = [
  "./",
];

let css_config = [];
let sass_config = [];
let js_config = [];

drupal_modules.forEach((val, i) => {
  // css_config.push({
  //   src: val + 'assets/src/css/**/*.css',
  //   dest: val + 'assets/dist/css',
  // });

  sass_config.push({
    src: val + 'assets/src/scss/*.scss',
    dest: val + 'assets/dist/css',
  });

  js_config.push({
    src: val + 'assets/src/js/**/*.js',
    dest: val + 'assets/dist/js',
  });
});

module.exports = {
  css: css_config,
  sass: sass_config,
  js: js_config,
  rollup: {
    outputOptions: {
      format: 'iife'
    }
  }
}

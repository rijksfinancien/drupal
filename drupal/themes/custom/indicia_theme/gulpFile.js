const gulp = require('gulp');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const concat = require('gulp-concat');
const autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync').create();
const rename = require('gulp-rename');
const header = require('gulp-header');
let uglify = require('gulp-uglify-es').default;

sass.compiler = require('node-sass');

// Copy config JSON to gulpSettings.json to change the settings.
let config;
try {
  config = require('./gulpSettings.json');
}
catch (error) {
  console.log(' ---------------------------------------', '\n', '   No config found. Using default.', '\n', '---------------------------------------');
  config = {
    "siteUrl": "minfin2020.local",
    "browserSyncPort": 12345,
    "browsers": [
      "firefox"
    ]
  }
}

gulp.task('sass', function () {
  return gulp.src('./components/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(header('@import \'_variables.scss\';\n'))
    .pipe(header('@import \'_breakpoints.scss\';\n'))
    .pipe(sass.sync({
      outputStyle: 'compressed',
      precision: 2,
      includePaths: './components/_globals/'
    }).on('error', sass.logError))
    .pipe(autoprefixer({cascade: false}))
    .pipe(concat('indicia.css'))
    .pipe(sourcemaps.write('maps'))
    .pipe(gulp.dest('./dist/css'))
    .pipe(browserSync.stream());
});

// Compiles all JS in one file.
gulp.task('js', function () {
  return gulp.src('./components/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(concat('indicia.min.js'))
    .pipe(uglify({mangle: true}))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./dist/js'))
    .pipe(browserSync.stream());
});

gulp.task('reload-twig', function () {
  //Requires a file to start the pipe.
  return gulp.src('./indicia_theme.theme').pipe(browserSync.stream());
});

gulp.task('copy-fonts', function () {
  return gulp.src('./components/**/fonts/*.*').pipe(rename({dirname: ''})).pipe(gulp.dest('./dist/css/fonts'));
});

gulp.task('browserSync', function () {
  return browserSync.init({
    proxy: config.siteUrl,
    port: config.browserSyncPort,
    baseDir: "./",
    open: true,
    notify: false,
    browser: config.browsers
  });
});

gulp.task('watcher', function () {
  console.log('Watching scss, js,fonts, twig');
  gulp.watch('./components/**/fonts/*.*', gulp.series(['copy-fonts']));
  gulp.watch('./components/**/*.scss', gulp.series(['sass']));
  gulp.watch('./components/**/*.js', gulp.series(['js']));
  gulp.watch('./components/**/*.twig', gulp.series(['reload-twig']));
});

// compile once
gulp.task('build', gulp.series(['sass', 'js', 'copy-fonts']));

// start the watcher.
gulp.task('watch', gulp.series('build', 'watcher'));

// Serves with browsersync.
gulp.task('serve', gulp.parallel('browserSync', 'watch'));

// Defaults to serve
gulp.task('default', gulp.series(['serve']));

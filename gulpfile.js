/** @todo Use Webpack 2 (handle other assets's types) **/

var gulp = require('gulp');

var jshint      = require('gulp-jshint'),
    sass        = require('gulp-sass'),
    path        = require('path'),
    sourcemaps  = require("gulp-sourcemaps"),
    babel       = require("gulp-babel"),
    concat      = require("gulp-concat")
    phpunit = require('gulp-phpunit');

var source = {
    js:'ressources/assets/js/',
    sass:'ressources/assets/sass/',
    php:['app/*.*','app/*/*.*','test/*/*.*']
};

var dist = {js:'public/assets/js/',css:'public/assets/css/'};

// Lint JS
gulp.task('lint', function () {
    gulp.src(source.js +'*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});

gulp.task('js', function () {
    gulp.src(source.js+"*.js")
        .pipe(sourcemaps.init())
        .pipe(babel())
        .pipe(concat("all.js"))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest(dist.js));
});

// Compile SASS
gulp.task('sass', function () {
    gulp.src(source.sass+'*.scss')
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(gulp.dest(dist.css));
});

// PHPUnit
gulp.task('phpunit', function() {
    gulp.src('phpunit.xml')
        .pipe(phpunit("%APPDATA%/Composer/vendor/bin/phpunit"));
});

gulp.task('build', ['sass','js','phpunit']);

// Default
gulp.task('default', ['build']);

// Watcher
gulp.task('watch', function () {
    gulp.watch(source.sass + '*.scss', ['sass']);
    gulp.watch(source.js + '*.js', ['lint', 'js']);
    gulp.watch(source.php, ['phpunit']);
});
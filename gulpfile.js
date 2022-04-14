const { src, dest, watch, parallel, series } = require("gulp");

const sass = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');
const minify = require('gulp-minify');
const cleanCss = require('gulp-clean-css');
const sync = require("browser-sync").create();
// const filter = require("gulp-filter");
const del = require("del");

function miniJS(cb) {
    src(['src/js/admin.js', 'src/js/add_template.js', 'src/js/statistics.js'])
        // .pipe(concat('bundle.js'))
        .pipe(minify({noSource: true}))
        // .pipe(filter(['src/js/admin.js', 'src/js/bundle-min.js']))
        .pipe(dest('views/js'))
        .on('end', function() {
            del(['views/js/bundle.js', 'views/js/bundle-min.js']);
        })
        .pipe(sync.stream())
    cb();
}

function miniCSS(cb) {
    src(['src/css/*.scss'])
        // .pipe(concat('stylesheet.css'))
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCss())
        .pipe(dest('views/css'))
        .pipe(sync.stream());
    cb();
}

function watchFiles(cb) {
    watch('src/css/**.scss', miniCSS);
    watch('src/js/**.js', miniJS).on('change', sync.reload);;
}

function browserSync(cb) {
    // Initialize BrowserSync
    sync.init({
        proxy: 'presta17/admin-dev',
        host: 'presta17/admin-dev',
        port: 8383
    });

    watch('src/css/**.scss', miniCSS);
    watch('src/js/**.js', miniJS);
}

exports.css = miniCSS;
exports.html = miniJS;
exports.watch = watchFiles;
exports.sync = browserSync;

exports.default = series(parallel(miniCSS, miniJS));

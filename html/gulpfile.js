const gulp = require('gulp');
const spawn = require('child_process').spawn;

// Install

gulp.task('cdn-install', function (done) {
  spawn('pnpm', ['install'], { cwd: './client/cdn' }).on('close', done);
});

gulp.task('admin-install', function (done) {
  spawn('pnpm', ['install'], { cwd: './client/admin' }).on('close', done);
});


// Update

gulp.task('cdn-update', function (done) {
  spawn('pnpm', ['update'], { cwd: './client/cdn' }).on('close', done);
});

gulp.task('admin-update', function (done) {
  spawn('pnpm', ['update'], { cwd: './client/admin' }).on('close', done);
});

// Dev

gulp.task('cdn-dev', function (done) {
  spawn('pnpm', ['run', 'dev'], { cwd: './client/cdn' }).on('close', done);
});

gulp.task('admin-dev', function (done) {
  spawn('pnpm', ['run', 'dev'], { cwd: './client/admin' }).on('close', done);
});

// Build

gulp.task('cdn-build', function (done) {
  spawn('pnpm', ['run', 'build'], { cwd: './client/cdn' }).on('close', done);
});

gulp.task('admin-build', function (done) {
  spawn('pnpm', ['run', 'build'], { cwd: './client/admin' }).on('close', done);
});

// CF Cache Purge

gulp.task('cf_cache_purge', function (done) {
  spawn('/bin/php', ['cf_cache_purge.php'], { cwd: './scripts' }).on('close', done);
});

gulp.task('install', gulp.parallel('cdn-install', 'admin-install'));
gulp.task('update', gulp.parallel('cdn-update', 'admin-update'));
gulp.task('dev', gulp.parallel('cdn-dev', 'admin-dev'));
gulp.task('build', gulp.series(gulp.parallel('cdn-build', 'admin-build'), 'cf_cache_purge'));

const gulp = require('gulp');
const spawn = require('child_process').spawn;

gulp.task('cdn-install', function (done) {
  spawn('pnpm', ['install'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('admin-install', function (done) {
  spawn('pnpm', ['install'], { cwd: '/var/www/html/client/admin' }).on('close', done);
});

gulp.task('cdn-dev', function (done) {
  spawn('pnpm', ['run', 'dev'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('admin-dev', function (done) {
  spawn('pnpm', ['run', 'dev'], { cwd: '/var/www/html/client/admin' }).on('close', done);
});

gulp.task('cdn-build', function (done) {
  spawn('pnpm', ['run', 'build'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('admin-build', function (done) {
  spawn('pnpm', ['run', 'build'], { cwd: '/var/www/html/client/admin' }).on('close', done);
});

gulp.task('cf_cache_purge', function (done) {
  spawn('/bin/php', ['cf_cache_purge.php'], { cwd: '/var/www/html/scripts' }).on('close', done);
});

gulp.task('install', gulp.parallel('cdn-install', 'admin-install'));
gulp.task('dev', gulp.parallel('cdn-dev', 'admin-dev'));
gulp.task('build', gulp.series(gulp.parallel('cdn-build', 'admin-build'), 'cf_cache_purge'));


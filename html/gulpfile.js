const gulp = require('gulp');
const spawn = require('child_process').spawn;

gulp.task('cdn-install', function(done) {
  spawn('pnpm', ['install'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('cdn-dev', function(done) {
  spawn('pnpm', ['run', 'dev'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('cdn-build', function(done) {
    spawn('pnpm', ['run', 'build'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('install', gulp.parallel('cdn-install'));
gulp.task('dev', gulp.parallel('cdn-dev'));
gulp.task('build', gulp.parallel('cdn-build'));
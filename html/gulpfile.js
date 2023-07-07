const gulp = require('gulp');
const spawn = require('child_process').spawn;

gulp.task('cdn-dev', function(done) {
  spawn('npm', ['run', 'dev'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('cdn-build', function(done) {
    spawn('npm', ['run', 'build'], { cwd: '/var/www/html/client/cdn' }).on('close', done);
});

gulp.task('dev', gulp.parallel('cdn-dev'));
gulp.task('build', gulp.parallel('cdn-build'));
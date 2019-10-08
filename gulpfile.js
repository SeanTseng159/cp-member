const gulp        = require('gulp');
const copy        = require('gulp-copy');
const clean       = require('gulp-clean');
const runSequence = require('run-sequence');
const fs          = require('fs');
const deb         = require('gulp-deb');
const dateFormat  = require('dateformat');

var debRoot = './deb/root';
var installPath = '/var/www/city-pass-member-center';
var pkg = JSON.parse(fs.readFileSync('./package.json'));

gulp.task('clean', function() {
    return gulp.src([
        debRoot + installPath + '/*',
        './dist/*',
        './tmp/*',
        '!.gitignore'
    ]).pipe(clean({ force: true }));
});

gulp.task('filter', function() {
    return gulp.src([
        '.env.example',
        'app/**/*',
        'artisan',
        'bootstrap/**/*',
        'composer.json',
        'composer.lock',
        'config/**/*',
        'database/**/*',
        'module/**/*',
        'package.json',
        'public/**/*',
        '!public/storage',
        'resources/**/*',
        'routes/**/*',
        'server.php',
        'src/**/*',
        'storage/app/public/.gitignore',
        'storage/framework/cache/.gitignore',
        'storage/framework/sessions/.gitignore',
        'storage/framework/views/.gitignore',
        'storage/logs/.gitignore',
        'vendor/**/*',
        '!.gitignore'
    ], { dot: true, allowEmpty:true}).pipe(copy(debRoot + installPath));
});

gulp.task('build', gulp.series('clean', 'filter', function() {
    return gulp.src([
            debRoot + '/**/*',
            '!.gitignore'
        ], { dot: true })
            .pipe(deb(pkg.name + '_' + dateFormat(new Date(), 'yyyymmdd') + '.deb', {
                'name': pkg.name,
                'version': pkg.version + '-' + dateFormat(new Date(), 'yyyymmdd'),
                'maintainer': {
                    'name': 'jenkins',
                    'email': 'jenkins@touchcity.tw'
                },
                'short_description': pkg.description,
                'long_description': pkg.description,
                'depends': [
                    'supervisor'
                ],
                'scripts': {
                    'postinst': fs.readFileSync("./deb/scripts/postinst", "utf8")
                }
            })).pipe(gulp.dest('./dist'));
}));

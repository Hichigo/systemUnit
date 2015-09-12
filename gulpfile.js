var gulp = require('gulp');
var stylus = require('gulp-stylus');
var autoprefixer = require('gulp-autoprefixer');
var csscomb = require('gulp-csscomb');
var minifyCss = require('gulp-minify-css');
var rename = require("gulp-rename");
var jade = require('gulp-jade');
var coffee = require('gulp-coffee');
var uglify = require('gulp-uglify');
var notify = require("gulp-notify");


//////////////////////////////////////////////////////////////////// запуск
gulp.task('default', ['watch']);

//////////////////////////////////////////////////////////////////// задача watch
gulp.task('watch', function() {
	gulp.watch('dev/jade/*.jade', ['html']); // следим за jade файлами
	gulp.watch('dev/stylus/*.styl', ['css']); // следим за stylus файлами
	gulp.watch('dev/coffee/*.coffee', ['js']); // следим за jade файлами
});

//////////////////////////////////////////////////////////////////// задача для css
gulp.task('css', function () {
	gulp.src('dev/stylus/*.styl')
		.pipe(stylus()) // компилируе stylus в css
		.pipe(autoprefixer({
				browsers: ['last 5 versions'],
				cascade: false
		})) // добавляем необходимые преффиксы
		.pipe(csscomb()) // сортируем свойства
		.pipe(gulp.dest('build/css')) // сохраняем не сжатый css
		.pipe(minifyCss({compatibility: 'ie8'})) // сжимаем css
		.pipe(rename('style.min.css')) // переименовываем в сжатый css
		.pipe(gulp.dest('build/css'))
		.pipe(notify('Complete CSS!!!')); // сохраняем сжатый css
});

//////////////////////////////////////////////////////////////////// задача для html
gulp.task('html', function () {
	gulp.src('dev/jade/*.jade')
		.pipe(jade({
			pretty: true
		})) // компилируем jade в html
		.pipe(gulp.dest('./'))
		.pipe(notify('Complete HTML!!!')); // сохраняем html
});

//////////////////////////////////////////////////////////////////// задача для js
gulp.task('js', function () {
	gulp.src('dev/coffee/*.coffee')
		.pipe(coffee({bare: true})) // компилируем coffee в js
		.pipe(gulp.dest('dev/js')) //сохраняем промежуточный js
		.pipe(uglify()) //минифицируем
		.pipe(rename('script.min.js')) // переименовываем в сжатый js
		.pipe(gulp.dest('build/js'))
		.pipe(notify('Complete JS!!!')); // сохраняем js
});
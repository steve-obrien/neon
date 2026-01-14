const mix = require('laravel-mix');

mix.js('core.js', 'dist').vue({ version: 2 })
	.setPublicPath('form/assets')
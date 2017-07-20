try {
	window.$ = window.jQuery = require('jquery');
	Dropzone = require('dropzone');
	Clipboard = require('clipboard');
} catch (e) { }

$(function () {
	console.log('Starting application');

	new Clipboard('.clippy');
})

webpackJsonp([1],{

/***/ 13:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(3);
module.exports = __webpack_require__(4);


/***/ }),

/***/ 3:
/***/ (function(module, exports, __webpack_require__) {

try {
	window.$ = window.jQuery = __webpack_require__(2);
	Dropzone = __webpack_require__(1);
	Clipboard = __webpack_require__(0);
} catch (e) {}

$(function () {
	console.log('Starting application');

	new Clipboard('.clippy');
});

/***/ }),

/***/ 4:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })

},[13]);
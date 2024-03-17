import Alpine from 'alpinejs';
import { takeWhile } from 'lodash';
window.Alpine = Alpine;

import axios from 'axios';
window.axios = axios;

import moment from 'moment';
import 'moment/locale/fr';
moment.locale('fr');
window.moment = moment;
moment().format();


window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.response.use(function (response) {
	return response;
 }, function (error) {

	// Authenticated user
	if (error.response.status == 401) {
		window.location.href = BASE_URL+'/'
	}
	else {
		return Promise.reject(error);
	}
  });

import Dropzone from "dropzone";
window.Dropzone = Dropzone;
import "dropzone/dist/dropzone.css";


import.meta.glob([
  '../images/**',
]);


Alpine.start()

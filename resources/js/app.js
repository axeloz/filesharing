import Alpine from 'alpinejs';
import { takeWhile } from 'lodash';
window.Alpine = Alpine;

import axios from 'axios';
window.axios = axios;

import moment from 'moment';
window.moment = moment;
moment().format();


window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Dropzone from "dropzone";
window.Dropzone = Dropzone;
import "dropzone/dist/dropzone.css";


import.meta.glob([
  '../images/**',
]);


Alpine.start()

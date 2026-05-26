/**
 * Axios + CSRF. Echo/Reverb vive en resources/js/realtime/.
 */

import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

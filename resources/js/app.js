window.Vue = require('vue');

Vue.component('mail-web', require('./Pages/Homepage/Index.vue').default);

// eslint-disable-next-line no-unused-vars
const app = new Vue({
    el: '#app',
});

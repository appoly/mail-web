window.Vue = require('vue');

Vue.component('mail-web', require('./Pages/Homepage/Index.vue').default);

const app = new Vue({
    el: '#app',
});

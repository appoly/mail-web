import Vue from 'vue';

Vue.component('MailWeb', require('./Pages/Homepage/Index.vue').default);

// eslint-disable-next-line no-unused-vars
const app = new Vue({
    el: '#app',
});

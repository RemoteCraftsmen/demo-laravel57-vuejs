require('./bootstrap');

window.Vue = require('vue');

Vue.prototype.$eventHub = new Vue(); // Global event bus

import Vue from 'vue';

Vue.component('tasks-list', require('./components/TasksList.vue'));
Vue.component('confimation-modal', require('./components/ConfirmationModal.vue'));

const app = new Vue({
    el: '#app'
});

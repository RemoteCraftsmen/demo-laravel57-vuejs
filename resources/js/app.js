import Axios from 'axios';
require('./bootstrap');


window.Vue = require('vue');

Vue.prototype.$eventHub = new Vue(); // Global event bus

import Vue from 'vue';

Vue.prototype.$http = Axios

Vue.component('tasks-list', require('./components/TasksList.vue'));

const app = new Vue({
    el: '#app'
});

// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from "vue";
import App from "./App";
import router from "./router";
import vueResouce from "vue-resource";
import element from "element-ui";

Vue.config.productionTip = false

Vue.use(vueResouce)
Vue.use(element)

/* eslint-disable no-new */
new Vue({
	el: '#app',
	router,
	template: '<App/>',
	components: {App}
})

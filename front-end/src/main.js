// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router/index'
import vueResource from 'vue-resource'
import elementUi from 'element-ui'
import 'element-ui/lib/theme-default/index.css'
import VueECharts from 'vue-echarts'
import 'echarts'
import VueLocalStorage from 'vue-localstorage'
import store from './store'

Vue.component('chart', VueECharts)
Vue.config.productionTip = false
Vue.use(vueResource)
Vue.use(elementUi)
Vue.use(VueLocalStorage)

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  template: '<App/>',
  components: {App}
})

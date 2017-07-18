/**
 * Created by wanqianjun on 2017/7/17.
 */
import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    homeIndex: '0'
  },
  mutations: {
    updateHomeIndex (state, payload) {
      state.homeIndex = payload.homeIndex
    }
  }
})

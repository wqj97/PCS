import Vue from 'vue'
import Router from 'vue-router'
import Console from '@/components/Console/Console'
import Search from '@/components/Search/Search'
import SearchByName from '@/components/Search/SearchByName'
import SearchById from '@/components/Search/SearchById'
import Setting from '@/components/Setting/Setting'
import Analyze from '@/components/Analyze/Analyze'
Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Home',
      component: Analyze
    },
    {
      path: '/Console',
      name: 'Console',
      component: Console
    },
    {
      path: '/Search',
      name: 'Search',
      component: Search,
      children: [
        {
          path: 'byName',
          name: 'SearchByName',
          component: SearchByName
        },
        {
          path: 'byName/:name',
          name: 'SearchByNameWithParam',
          component: SearchByName
        },
        {
          path: 'byId',
          name: 'SearchByIdWithParam',
          component: SearchById
        },
        {
          path: 'byId/:id',
          name: 'SearchById',
          component: SearchById
        }
      ]
    },
    {
      path: '/Setting',
      name: 'Setting',
      component: Setting
    },
    {
      path: '/Analyze',
      name: 'Analyze',
      component: Analyze
    }
  ]
})

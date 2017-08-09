import Vue from 'vue'
import Router from 'vue-router'
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
      path: '/Analyze',
      name: 'Analyze',
      component: Analyze
    },
    {
      path: '/Analyze/:Id',
      name: 'AnalyzeViewer',
      component: () => import('@/components/Analyze/AnalyzeViewer')
    },
    {
      path: '/Console',
      name: 'Console',
      component: () => import('@/components/Console/Console')
    },
    {
      path: '/Search',
      name: 'Search',
      component: () => import('@/components/Search/Search'),
      children: [
        {
          path: 'byName',
          name: 'SearchByName',
          component: () => import('@/components/Search/SearchByName')
        },
        {
          path: 'byName/:name',
          name: 'SearchByNameWithParam',
          component: () => import('@/components/Search/SearchByName')
        },
        {
          path: 'byId',
          name: 'SearchByIdWithParam',
          component: () => import('@/components/Search/SearchById')
        },
        {
          path: 'byId/:id',
          name: 'SearchById',
          component: () => import('@/components/Search/SearchById')
        }
      ]
    },
    {
      path: '/Setting',
      name: 'Setting',
      component: () => import('@/components/Setting/Setting')
    }
  ]
})

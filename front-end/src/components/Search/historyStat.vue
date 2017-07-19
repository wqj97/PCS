<template>
  <div>
    <el-row>
      <el-col v-for="(query, index) in eachQuery" :key="index">
          <template v-for="store in query.storeInfo">
            <echarts :options="options(query)"></echarts>
          </template>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import 'echarts'
  import Echarts from 'vue-echarts/components/ECharts'
  import Store from '../model/Store'
  //  import ecStat from 'echarts-stat'
  export default {
    name: 'historyStat',
    components: {
      Echarts
    },
    props: {
      history: {
        required: true,
        type: Array
      }
    },
    data () {
      return {
        eachQuery: []
      }
    },
    computed: {
      options (query) {
        return {
          backgroundColor: '#2c343c',
          title: {
            text: query.lastUpdateTime,
            left: 'center',
            top: 20,
            textStyle: {
              color: '#ccc'
            }
          }
        }
      }
    },
    watch: {
      history () {
        this.eachQuery = []
        this.history.forEach(val => {
          let Jd = JSON.parse(val.P_Jddj_info)

          let lastUpdateTime = val.P_last_update
          let storeInfo = []
          if (Jd[0] === null) {
            this.eachQuery.push({
              lastUpdateTime: lastUpdateTime,
              storeInfo: storeInfo
            })
            return
          }
          Jd.forEach(val => {
            let store = new Store(val.store_name, val.city)
            val.products.forEach(val => {
              store.addProduct(val.product_name, val.product_price)
            })
            storeInfo.push(store)
          })
          this.eachQuery.push({
            lastUpdateTime: lastUpdateTime,
            storeInfo: storeInfo
          })
        })
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>

</style>

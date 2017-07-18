<template>
  <div>
    {{history}}
  </div>
</template>
<script>
  import 'echarts'
  import Echarts from 'vue-echarts/components/ECharts'
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
        store: []
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>

</style>

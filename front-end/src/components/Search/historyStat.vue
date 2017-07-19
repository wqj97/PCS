<template>
  <div>
    <template v-for="(eachTime, productName, index) in products">
      <el-row>
        <el-col>
          <echarts :options="options(eachTime)"></echarts>
        </el-col>
      </el-row>

    </template>
  </div>
</template>
<script>
  import 'echarts'
  import Echarts from 'vue-echarts/components/ECharts'
  import ecStat from 'echarts-stat'

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
        products: {}
      }
    },
    methods: {
      options (eachTime) {
        let prices = []
        eachTime.forEach(val => {
          prices.push([
            new Date(val.last_update_time).getTime(), val.product_price
          ])
        })
        let myRegression = ecStat.regression('polynomial', prices, 3)
        console.log(myRegression)
        return {
          backgroundColor: '#2c343c',
          width: '100%',
          title: {
            text: eachTime[0].product_name,
            left: 'center',
            top: 20,
            textStyle: {
              color: '#ccc'
            }
          },
          xAxis: {
            type: 'value',
            splitLine: {
              lineStyle: {
                type: 'dashed'
              }
            }
          },
          yAxis: {
            type: 'value',
            splitLine: {
              lineStyle: {
                type: 'dashed'
              }
            }
          },
          series: [{
            name: 'scatter',
            type: 'scatter',
            label: {
              emphasis: {
                show: true
              }
            },
            data: prices
          }, {
            name: 'line',
            type: 'line',
            smooth: true,
            showSymbol: false,
            data: myRegression.points,
            markPoint: {
              itemStyle: {
                normal: {
                  color: 'transparent'
                }
              },
              label: {
                normal: {
                  show: true,
                  position: 'left',
                  formatter: myRegression.expression,
                  textStyle: {
                    color: '#333',
                    fontSize: 14
                  }
                }
              },
              data: [{
                coord: myRegression.points[myRegression.points.length - 1]
              }]
            }
          }]
        }
      }
    },
    watch: {
      history () {
        let productTemp = {}
        this.history.forEach(val => {
          let Jd = JSON.parse(val.P_Jddj_info)

          let lastUpdateTime = val.P_last_update

          if (Jd[0] === null) return

          Jd.forEach(val => {
            val.products.forEach(product => {
              let productEach = {
                product_name: product.product_name,
                product_price: product.product_price,
                store_info: {
                  city: val.city,
                  store_name: val.store_name
                },
                last_update_time: lastUpdateTime
              }

              if (product.product_name in this.products) {
                productTemp[product.product_name].push(productEach)
              } else {
                productTemp[product.product_name] = [productEach]
              }
            })
          })
        })
        this.products = productTemp
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>

</style>

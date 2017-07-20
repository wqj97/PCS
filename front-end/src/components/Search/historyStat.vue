<template>
  <div>
    <el-card class="search-box box-card">
      <div class="sub-title">
        历史数据
        <span class="tub-title">
            拟合度:
          </span>
        <el-slider
            v-model="polynomial"
            :step="1"
            :min="1"
            :max="10"
            show-stops>
        </el-slider>
      </div>
      <el-collapse v-model="activeName" accordion>
        <template v-for="(eachTime, productName, index) in products">
          <el-collapse-item :title="productName" :name="index">
            <transition name="el-fade-in">
              <echarts class="echart" :options="options(eachTime)" v-if="activeName == index"></echarts>
            </transition>
          </el-collapse-item>
        </template>
      </el-collapse>
    </el-card>
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
        products: {},
        activeName: 0,
        polynomial: 3
      }
    },
    mounted () {
      this.applyHistory()
    },
    methods: {
      options (eachTime) {
        let prices = []
        let TimeLabel = []
        let priceWithTimeLabel = []
        eachTime.forEach((val, key) => {
          prices.push([key, val.product_price])
          priceWithTimeLabel.push([
            val.last_update_time, val.product_price
          ])
          TimeLabel.push(val.last_update_time)
        })
        let myRegression = ecStat.regression('polynomial', prices, this.polynomial)
        return {
          backgroundColor: '#2c343c',
          legend: {
            width: '100%',
            height: '400px'
          },
          title: {
            text: eachTime[0].product_name,
            left: 'center',
            top: 20,
            textStyle: {
              color: '#ccc'
            }
          },
          tooltip: {
            trigger: 'axis',
            formatter (params) {
              let date = new Date(params[0].data[0])
              let value = params[1].data[1]
              let rawValue = params[0].data[1]
              return date.toLocaleString() + `<br />原始值: ${rawValue}, 平缓值: ${value}`
            },
            axisPointer: {
              animation: false
            }
          },
          xAxis: {
            type: 'category',
            data: TimeLabel,
            name: '时间',
            scale: true,
            position: 'bottom',
            splitLine: {
              show: false,
              lineStyle: {
                color: '#fff',
                type: 'solid'
              }
            }
          },
          yAxis: {
            type: 'value',
            scale: true,
            splitLine: {
              show: false,
              lineStyle: {
                type: 'solid'
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
            data: priceWithTimeLabel
          }, {
            name: 'line',
            type: 'line',
            lineStyle: {
              normal: {
                width: 3,
                shadowColor: 'rgba(237,200,71,0.5)',
                shadowBlur: 50,
                shadowOffsetY: 0,
                color: {
                  type: 'linear',
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [{
                    offset: 0, color: '#ee5c2d' // 0% 处的颜色
                  }, {
                    offset: 1, color: '#2bcc30' // 100% 处的颜色
                  }],
                  globalCoord: false // 缺省为 false
                }
              }
            },
            smooth: true,
            showSymbol: true,
            data: myRegression.points
          }]
        }
      },
      applyHistory () {
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

              if (product.product_name in productTemp) {
                productTemp[product.product_name].push(productEach)
              } else {
                productTemp[product.product_name] = [productEach]
              }
            })
          })
        })
        this.products = productTemp
      }
    },
    watch: {
      history () {
        this.applyHistory()
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>
  .echart {
    width: 100%;
  }
</style>

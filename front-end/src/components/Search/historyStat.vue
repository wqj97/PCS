<template>
  <div>
    <el-card class="search-box">
      <div class="sub-title">
        历史数据
        <span class="tub-title">
            拟合函数: {{expression}}
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
        polynomial: 3,
        expression: ''
      }
    },
    mounted () {
      this.applyHistory()
    },
    methods: {
      options (eachTime) {
        let prices = []
        let TimeLabel = []
        eachTime.forEach((val, key) => {
          prices.push([key, Number(val.product_price)])
          TimeLabel.push([new Date(val.last_update_time).toLocaleDateString()])
        })
        let myRegression = ecStat.regression('polynomial', prices, this.polynomial)
        this.expression = myRegression.expression
        let series = [{
          name: '实际价格',
          type: 'scatter',
          label: {
            emphasis: {
              show: true
            }
          },
          data: prices
        }, {
          name: '预测价格',
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
                  offset: 0.5, color: '#fff' // 100% 处的颜色
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
              let price = params[0].data[1]
              let predict = params[1].data[1]
              return `采集价格: ${price}, 预测值: ${predict.toFixed(2)}`
            },
            axisPointer: {
              animation: false
            }
          },
          xAxis: {
            type: 'category',
            data: TimeLabel,
            boundaryGap: false,
            name: '采集时间',
            nameTextStyle: {
              color: '#fff'
            },
            axisLine: {
              lineStyle: {
                color: '#fff'
              }
            }
          },
          yAxis: {
            type: 'value',
            name: '价格',
            scale: true,
            splitLine: {
              show: false,
              lineStyle: {
                type: 'solid'
              }
            },
            nameTextStyle: {
              color: '#fff'
            },
            axisLine: {
              lineStyle: {
                color: '#fff'
              }
            }
          },
          series: series
        }
      },
      applyHistory () {
        let productTemp = {}
        this.history.forEach(val => {
          let Jd = JSON.parse(val.P_Jddj_info)
          let Tbk = JSON.parse(val.P_Tbk_info)
          let lastUpdateTime = val.P_last_update

          if (Jd && Jd[0]) {
            Jd.forEach(val => {
              val.products.forEach(product => {
                let productEach = {
                  product_name: product.product_name,
                  product_price: product.product_price,
                  store_info: {
                    city: val.city,
                    store_name: val.store_name
                  },
                  last_update_time: lastUpdateTime,
                  label: 'Jddj'
                }

                if (product.product_name in productTemp) {
                  productTemp[product.product_name].push(productEach)
                } else {
                  productTemp[product.product_name] = [productEach]
                }
              })
            })
          }
          if (Tbk && Tbk[0]) {
            console.log('hello tbk', Tbk[0].products)
            Tbk[0].products.forEach(product => {
              let productEach = {
                product_name: product.product_name,
                product_price: product.product_price,
                store_info: {
                  city: Tbk[0].city,
                  store_name: Tbk[0].store_name
                },
                last_update_time: lastUpdateTime,
                label: 'Tbk'
              }

              if (product.product_name in productTemp) {
                productTemp[product.product_name].push(productEach)
              } else {
                productTemp[product.product_name] = [productEach]
              }
            })
          }
        })
        let tmp = {}
        Object.keys(productTemp).sort((val1, val2) => {
          return productTemp[val2].length - productTemp[val1].length
        }).forEach(val => {
          tmp[val] = productTemp[val]
        })
        this.products = tmp
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

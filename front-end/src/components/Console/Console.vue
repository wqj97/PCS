<template>
  <el-row>
    <el-row>
      <el-col :span="12">
        <el-card class="box-card">
          <div class="main-title">
            CPU使用
          </div>
          <echarts :options="cpuOptions"></echarts>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card class="box-card">
          <div class="main-title">
            内存使用
          </div>
          <echarts :options="memoryOpitons"></echarts>
        </el-card>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="24">
        <el-card class="box-card">
          <div class="main-title">
            可用代理
          </div>
          <echarts :options="proxyOpitons"></echarts>
        </el-card>
      </el-col>
    </el-row>
  </el-row>
</template>
<script>
  import 'echarts'
  import Echarts from 'vue-echarts/components/ECharts'

  export default {
    name: 'Console',
    components: {
      Echarts
    },
    data () {
      return {
        cpu: [],
        memory: [],
        proxyPool: [],
        timeLabel: []
      }
    },
    mounted () {
      this.$store.commit('updateHomeIndex', {homeIndex: '0'})
      this.getSystemStatus()
      this.getSystemStatus()
      setInterval(() => {
        this.getSystemStatus()
      }, 5000)
    },
    methods: {
      getSystemStatus () {
        this.$http.get('/console').then(data => {
          data = data.body
          this.cpu.push(data.cpu)
          this.memory.push(data.memory * 100)
          this.proxyPool.push(data.proxyPoolStatus)
          this.timeLabel.push(new Date().toLocaleTimeString())
        })
      }
    },
    computed: {
      cpuOptions () {
        return {
          legend: {
            width: '100%'
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            data: this.timeLabel
          },
          yAxis: {
            type: 'value',
            scale: true,
            splitNumber: 0.5
          },
          series: {
            name: 'cpu占用',
            type: 'line',
            stack: '总量',
            areaStyle: {normal: {}},
            data: this.cpu
          }
        }
      },
      memoryOpitons () {
        return {
          legend: {
            width: '100%'
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            data: this.timeLabel
          },
          yAxis: {
            type: 'value',
            max: 100,
            min: 0
          },
          series: {
            name: '内存占用',
            type: 'line',
            stack: '总量',
            lineStyle: {
              normal: {
                color: {
                  type: 'linear',
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [{
                    offset: 0, color: '#2a88ff' // 0% 处的颜色
                  }, {
                    offset: 1, color: '#2a88ff' // 100% 处的颜色
                  }]
                }
              }
            },
            areaStyle: {
              normal: {
                color: {
                  type: 'linear',
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [{
                    offset: 0, color: '#2a88ff' // 0% 处的颜色
                  }, {
                    offset: 1, color: '#2a88ff' // 100% 处的颜色
                  }]
                }
              }
            },
            data: this.memory
          }
        }
      },
      proxyOpitons () {
        return {
          legend: {
            width: '100%'
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            data: this.timeLabel
          },
          yAxis: {
            type: 'value',
            scale: true,
            splitNumber: 1
          },
          series: {
            name: '可用代理数',
            type: 'line',
            stack: '总量',
            lineStyle: {
              normal: {
                color: {
                  type: 'linear',
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [{
                    offset: 0, color: '#41ff43' // 0% 处的颜色
                  }, {
                    offset: 1, color: '#41FF43' // 100% 处的颜色
                  }]
                }
              }
            },
            areaStyle: {
              normal: {
                color: {
                  type: 'linear',
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [{
                    offset: 0, color: '#41FF43' // 0% 处的颜色
                  }, {
                    offset: 1, color: '#41FF43' // 100% 处的颜色
                  }]
                }
              }
            },
            data: this.proxyPool
          }
        }
      }
    }
  }
</script>
<style type='text/scss' lang='scss' scoped>
  .box-card {
    margin: 15px;
  }

  .main-title {
    font-size: 18px;
    margin: 10px 0;
    color: #8492a6;
  }

  .echarts {
    width: 100%;
    div {
      width: 100%;
    }

  }
</style>

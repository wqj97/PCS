<template>
  <div v-loading.fullscreen.lock="fullscreenLoading">
    <el-row>
      <el-col :span="5">
        <el-card class="box-card">
          <span class="sub-title">
            自动更新:
          </span>
          <el-switch
              v-model="autoUpdate"
              on-color="#13ce66"
              off-color="#ff4949">
          </el-switch>
        </el-card>
      </el-col>
      <transition name="el-zoom-in-top">
        <el-col :span="9" v-if="autoUpdate">
          <el-card class="box-card">
          <span class="sub-title">
            数据过期时间 (小时):
          </span>
            <el-input-number v-model="autoUpdateFrequency" :min="1"></el-input-number>
          </el-card>
        </el-col>
      </transition>
      <el-col :span="10">
        <el-card class="box-card">
          <span class="sub-title">
            代理池延迟 (时间越长代理越稳定, 但整体访问速度越慢):
          </span>
          <el-input-number v-model="proxyDelay" :min="0" :step="0.1" :max="2"></el-input-number>
        </el-card>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="9" v-if="autoUpdate">
        <el-card class="box-card">
          <span class="sub-title">
            爬虫出错重试上线次数 (次数越大, 延迟可能越久, 也越有可能查询到结果):
          </span>
          <el-input-number v-model="retryTimes" :min="1"></el-input-number>
        </el-card>
      </el-col>
    </el-row>
    <el-row type="flex" justify="space-between">
      <el-col>
        <el-card class="box-card" style="display: inline-block">
          <div class="sub-title">系统只会从已选中的数据源中获取数据</div>
          <el-checkbox-group v-model="openedSpider" size="large" fill="#324057" text-color="#fbf42a" :min="1">
            <el-checkbox-button v-for="spider in spiderList" :label="spider.key" :disabled="spider.disabled"
                                :key="spider.key">
              {{spider.label}}
            </el-checkbox-button>
          </el-checkbox-group>
        </el-card>
      </el-col>
      <el-col>
        <el-card class="box-card">
          <div class="sub-title">京东到家设置</div>
          <el-form :model="Jddj" :rules="spiderOptionCheck">
            <el-form-item label="名称" prop="option">
              <el-input
                  type="textarea"
                  autosize
                  size="large"
                  v-model="Jddj.option">
              </el-input>
            </el-form-item>

          </el-form>
        </el-card>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="11">
        <el-card class="box-card" style="display: inline-block">
          <div class="sub-title">代理池
            <el-button @click="addIp" type="success" icon="plus">手工添加代理</el-button>
            <el-button @click="testingBlock = !testingBlock" type="primary" icon="d-arrow-right">快速测试</el-button>
          </div>
          <el-transfer v-model="proxyPoolFreezed" :data="proxyPool" :titles="['正在使用的代理','停止使用的代理']"></el-transfer>
          <div class="sub-title">
            <el-button type="primary" @click="saveProxyPool">保存</el-button>
          </div>
        </el-card>
      </el-col>
      <transition name="el-fade-in-linear">
        <el-col :span="13" v-if="testingBlock">
          <el-card class="box-card" style="height: 455px">
            <el-row type="flex" justify="space-between">
              <el-col>
                <span class="sub-title">代理测速:</span>
                <el-button type="danger" @click="startTesting">开始测速</el-button>
              </el-col>
              <el-col v-if="proxyTestPercentage">
                <span class="sub-title">移除不可用的代理:</span>
                <el-button type="danger" @click="dialogFormVisible = true">点击移除</el-button>
                <el-dialog title="停用延迟高于设定的代理" :visible.sync="dialogFormVisible">
                  <div class="sub-title">
                    当设定值为0时, 只移除失效的代理
                  </div>
                  <el-slider v-model="delaySetting" :step="100" :min="0" :max="5000" show-input></el-slider>
                  <div slot="footer" class="dialog-footer">
                    <el-button @click="dialogFormVisible = false">取 消</el-button>
                    <el-button type="primary" @click="removeLowProxy">确 定</el-button>
                  </div>
                </el-dialog>
              </el-col>
            </el-row>
            <div class="sub-title">
              <el-progress :text-inside="true" :stroke-width="18" :percentage="Math.ceil(proxyTestPercentage)"
                           v-show="proxyTestPercentage"></el-progress>
            </div>
            <el-table
                :data="proxyPool"
                height="340"
                style="width: 100%">
              <el-table-column
                  prop="label"
                  label="地址">
              </el-table-column>
              <el-table-column
                  prop="delay"
                  sortable
                  label="延迟">
                <template scope="delay">
                  <span class="low-delay" :class="{'hight-delay': isHeight(delay.row.delay)}">{{delay.row.delay}}</span>
                </template>
              </el-table-column>
            </el-table>
          </el-card>
        </el-col>
      </transition>
    </el-row>
    <el-row>
      <el-card class="box-card">
        <div class="sub-title">系统只会允许从下列地区获取信息:
          <el-button type="success" @click="newPositionDialog = true">添加位置
          </el-button>
        </div>
        <el-dialog title="添加位置" :visible.sync="newPositionDialog">
          <el-form :model="newPosition">
            <el-form-item label="位置名称">
              <el-input v-model="newPosition.label"></el-input>
            </el-form-item>
            <el-form-item label="位置经纬度">
              <el-input v-model="newPosition.longitude">
                <template slot="prepend">经度</template>
              </el-input>
              <el-input v-model="newPosition.latitude">
                <template slot="prepend">纬度</template>
              </el-input>
            </el-form-item>
            <el-form-item label="对应城市Id">
              <el-input v-model="newPosition.cityId.Jddj">
                <template slot="prepend">京东到家</template>
              </el-input>
              <el-input v-model="newPosition.cityId.Tm">
                <template slot="prepend">天猫超市</template>
              </el-input>
              <el-input v-model="newPosition.cityId.Tb">
                <template slot="prepend">淘宝</template>
              </el-input>
              <el-input v-model="newPosition.cityId.Sdg">
                <template slot="prepend">闪电购</template>
              </el-input>
            </el-form-item>
          </el-form>
          <div slot="footer" class="dialog-footer">
            <el-button @click="newPositionDialog = false">取 消</el-button>
            <el-button type="primary" @click="addPosition">确 定</el-button>
          </div>
        </el-dialog>
        <template v-for="(city, index) in cities">
          <el-col :span="12">
            <el-card style="display: inline-block" class="box-card position-card">
              <el-form :model="city" :rules="cityRules" label-width="100px" class="demo-ruleForm">
                <el-form-item label="城市名称" prop="label">
                  <el-input v-model="city.label"></el-input>
                </el-form-item>
                <el-form-item label="城市经度" prop="latitude">
                  <el-input v-model="city.latitude"></el-input>
                </el-form-item>
                <el-form-item label="城市纬度" prop="longitude">
                  <el-input v-model="city.longitude"></el-input>
                </el-form-item>
                <el-form-item label="对应城市Id" prop="cityId">
                  <el-input v-for="(val, key, index) in city.cityId" :key="index" v-model="city.cityId[key]">
                    <template slot="prepend">{{spiderList[index].label}}</template>
                  </el-input>
                </el-form-item>
              </el-form>
              <i class="el-icon-circle-close remove-icon" @click="addCity(index)"></i>
            </el-card>
          </el-col>
        </template>
      </el-card>
    </el-row>
  </div>
</template>
<script>
  import _ from 'lodash'

  export default {
    name: 'Setting',
    data () {
      let JSONcheck = (rule, value, cb) => {
        if (this.startCheck === false) {
          this.startCheck = true
          return
        }
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          try {
            JSON.parse(value)
          } catch (err) {
            cb(new Error(err))
            return
          }
          this.saveSetting('Jddj', JSON.stringify(JSON.parse(value)))
        }, 500)
      }
      return {
        autoUpdate: true,
        fullscreenLoading: true,
        autoUpdateFrequency: 2,
        openedSpider: [],
        proxyTestPercentage: 0,
        retryTimes: 10,
        proxyDelay: 0.1,
        testingBlock: false,
        Jddj: {
          option: ''
        },
        spiderList: [
          {
            label: '京东到家',
            key: 'Jddj'
          },
          {
            label: '天猫超市',
            key: 'Tmcs',
            disabled: true
          },
          {
            label: '淘宝',
            key: 'Tb',
            disabled: true
          },
          {
            label: '闪电购',
            key: 'Sdg',
            disabled: true
          }
        ],
        cities: [],
        newPosition: {
          label: '',
          latitude: '',
          longitude: '',
          cityId: {
            Jddj: '',
            Tm: '',
            Tb: '',
            Sdg: ''
          }
        },
        newPositionDialog: false,
        dialogFormVisible: false,
        delaySetting: 0,
        proxyPool: [],
        proxyPoolFreezed: [],
        cityRules: {
          label: {
            required: true,
            message: '必须输入城市名'
          },
          latitude: {
            required: true,
            message: '必须输入经度'
          },
          longitude: {
            required: true,
            message: '必须输入纬度'
          }
        },
        timeout: '',
        startCheck: false,
        spiderOptionCheck: {
          option: [{
            validator: JSONcheck
          }]
        }
      }
    },
    mounted () {
      this.$store.commit('updateHomeIndex', {homeIndex: '4'})
      this.$http.get('/queryer/setting/list').then(data => {
        this.fullscreenLoading = false
        // 代理池
        data.body.proxy_pool.forEach(val => {
          this.proxyPool.push({
            label: val === '' ? '本机' : val,
            key: val
          })
        })
        data.body.proxy_pool_freezed.forEach(val => {
          this.proxyPoolFreezed.push(val)
          this.proxyPool.push({label: val, key: val})
        })
        this.proxyDelay = data.body.proxy_sleep
        // 启动的爬虫
        this.openedSpider = data.body.opened_spider
        // 重试次数
        this.retryTimes = data.body.retry_times
        this.$watch('retryTimes', this.saveRetryTimes, {
          deep: true
        })
        // 京东到家
        this.Jddj.option = JSON.stringify(data.body.Jddj, null, 8)
        // 自动更新
        this.autoUpdate = Boolean(data.body.auto_update)
        this.autoUpdateFrequency = data.body.auto_update_frequency / 3600
        // 城市
        _.forIn(data.body.city, (val, key) => {
          this.cities.push({
            label: key,
            longitude: val[0],
            latitude: val[1],
            cityId: {
              Jddj: val[2][0],
              Tm: val[2][1],
              Tb: val[2][2],
              Sdg: val[2][3]
            }
          })
        })
        this.$watch('cities', this.saveCities, {
          deep: true
        })
      })
    },
    methods: {
      saveSetting (key, val) {
        this.$http.post('/queryer/setting/set', {
          key: key,
          val: val
        }).then(data => {
          if (data.status === 200) {
            this.$notify({
              title: '成功',
              message: '设置保存成功',
              type: 'success'
            })
          }
        })
      },
      saveRetryTimes () {
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          this.saveSetting('retry_times', this.retryTimes)
        }, 500)
      },
      saveCities () {
        let setting = {}
        _.forIn(this.cities, val => {
          setting[val.label] = []
          setting[val.label][0] = val.longitude
          setting[val.label][1] = val.latitude
          setting[val.label][2] = [val.cityId['Jddj'], val.cityId['Tm'], val.cityId['Tb'], val.cityId['Sdg']]
        })
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          this.saveSetting('city', JSON.stringify(setting))
        }, 2000)
      },
      addIp () {
        this.$prompt('请输入一个有效的Ip地址', '提示', {
          confirmButtonText: '自动验证',
          cancelButtonText: '取消',
          inputPattern: /\d+\.\d+\.\d+\.\d+:\d+/,
          inputErrorMessage: '代理格式不正确, 正确示例: 127.0.0.1:8080'
        }).then(({value}) => {
          let proxyPool = this.proxyPool.map(val => {
            return val.label
          })
          if (_.intersection(proxyPool, [value]).length !== 0) {
            this.$message({
              type: 'error',
              message: 'Ip: ' + value + ' 已经存在于代理池中, 无需再次添加'
            })
            return
          }
          this.$http.post('/queryer/index/proxy', {
            ip: value
          }).then(data => {
            if (data.body.result === 'success') {
              this.proxyPool.push({
                label: value,
                key: value,
                dalay: data.body.delay
              })
              this.$message({
                type: 'success',
                message: 'Ip: ' + value + ' 添加成功, 延迟: ' + data.body.delay + 'ms'
              })
            } else {
              this.$message({
                type: 'error',
                message: 'Ip: ' + value + ' 添加失败, 代理无法使用, 或者返回了异常结果'
              })
            }
          })
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '取消添加'
          })
        })
      },
      addPosition () {
        this.newPositionDialog = false
        this.cities.push(this.newPosition)
        this.newPosition = {
          label: '',
          latitude: '',
          longitude: '',
          cityId: {
            Jddj: '',
            Tm: '',
            Tb: '',
            Sdg: ''
          }
        }
      },
      startTesting () {
        this.proxyPool.forEach((ip, key) => {
          this.$http.post('/queryer/index/proxy', {
            ip: ip.key
          }).then(data => {
            this.percentageIncrese()
            if (data.body.result === 'success') {
              this.$set(this.proxyPool, key, {label: ip.label, key: ip.key, delay: data.body.delay})
            } else {
              this.$set(this.proxyPool, key, {label: ip.label, key: ip.key, delay: 'Failed'})
            }
          })
        })
      },
      removeLowProxy () {
        this.dialogFormVisible = false
        this.proxyPool.forEach((val, key) => {
          if (val.delay === 'Failed') {
            this.proxyPoolFreezed.push(this.proxyPool[key].key)
          } else if (this.delaySetting === 0) {
            return
          } else if (val.delay > this.delaySetting) {
            this.proxyPoolFreezed.push(this.proxyPool[key].key)
          }
        })
      },
      percentageIncrese () {
        this.proxyTestPercentage += 100 / this.proxyPool.length
      },
      isHeight (delay) {
        if (typeof delay === 'string') {
          return true
        }
        return delay > 500
      },
      saveProxyPool () {
        let proxyPool = []
        this.proxyPool.forEach(val => {
          proxyPool.push(val.key)
        })
        proxyPool = _.difference(proxyPool, this.proxyPoolFreezed)
        this.saveSetting('proxy_pool', JSON.stringify(proxyPool))
        this.saveSetting('proxy_pool_freezed', JSON.stringify(this.proxyPoolFreezed))
      },
      addCity (index) {
        this.$confirm(`确认删除${this.cities[index].label}吗`).then(() => {
          this.cities.splice(index, 1)
        })
      }
    },
    watch: {
      autoUpdate (newValue) {
        this.saveSetting('auto_update', Number(newValue))
      },
      autoUpdateFrequency (newValue) {
        this.saveSetting('auto_update_frequency', newValue * 3600)
      },
      proxyDelay (newValue) {
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          this.saveSetting('proxy_sleep', newValue)
        }, 500)
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>
  .box-card {
    margin: 15px;
    position: relative;
  }

  .position-card {
    padding-top: 15px;
  }

  .sub-title {
    margin-right: 20px
  }

  .low-delay {
    color: #34ff3a
  }

  .hight-delay {
    color: #ff4834
  }

  .remove-icon {
    position: absolute;
    top: 15px;
    cursor: pointer;
    z-index: 999;
  }
</style>

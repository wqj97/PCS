<template>
  <div>
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
    </el-row>
    <el-row>
      <el-card class="box-card" style="display: inline-block">
        <div class="sub-title">系统只会从已选中的数据源中获取数据</div>
        <el-checkbox-group v-model="openedSpider" size="large" fill="#324057" text-color="#fbf42a" :min="1">
          <el-checkbox-button v-for="spider in spiderList" :label="spider.key" :disabled="spider.disabled"
                              :key="spider.key">
            {{spider.label}}
          </el-checkbox-button>
        </el-checkbox-group>
      </el-card>
    </el-row>
    <el-row>
      <el-col :span="10">
        <el-card class="box-card" style="display: inline-block">
          <div class="sub-title">代理池</div>
          <el-transfer v-model="proxyPoolFreezed" :data="proxyPool" :titles="['正在使用的代理','停止使用的代理']"></el-transfer>
        </el-card>
      </el-col>
      <el-col :span="14">
        <el-card class="box-card" style="height: 400px;">
          <div class="sub-title">代理测速:
            <el-button type="danger" @click="startTesting">开始测速</el-button>
          </div>
          <div class="sub-title">
            <el-progress :text-inside="true" :stroke-width="18" :percentage="ProxyTestPercentage"
                         v-show="ProxyTestPercentage"></el-progress>
          </div>
          <el-table
              :data="proxyPool"
              height="275"
              style="width: 100%">
            <el-table-column
                prop="label"
                label="地址">
            </el-table-column>
            <el-table-column
                prop="address"
                label="延迟">
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  export default {
    name: 'Setting',
    data () {
      return {
        autoUpdate: true,
        autoUpdateFrequency: 2,
        openedSpider: [],
        ProxyTestPercentage: 0,
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
        proxyPool: [],
        proxyPoolFreezed: []
      }
    },
    mounted () {
      this.$store.commit('updateHomeIndex', {homeIndex: '4'})
      this.$http.get('/queryer/setting/list').then(data => {
        // 代理池
        data.body.proxy_pool.forEach(val => {
          this.proxyPool.push({
            label: val === '' ? '本机' : val,
            key: val
          })
        })
        data.body.proxy_pool_freezed.forEach(val => {
          this.proxyPoolFreezed.push(val)
        })
        // 启动的爬虫
        this.openedSpider = data.body.opened_spider
        // 自动更新
        this.autoUpdate = Boolean(data.body.auto_update)
        this.autoUpdateFrequency = data.body.auto_update_frequency / 3600
      })
    },
    methods: {
      startTesting () {
        this.proxyPool.forEach(ip => {
          console.log(ip)
        })
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>
  .box-card {
    margin: 15px;
  }

  .sub-title {
    margin-right: 20px;
  }
</style>

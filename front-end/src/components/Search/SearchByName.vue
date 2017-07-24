<template>
  <div>
    <el-row>
      <el-col :span="8" class="search-box">
        <el-card class="box-card">
          <el-form :model="form">
            <el-form-item
                label="关键词"
                prop="productName"
                :rules="{ required: true, message: '关键词不能为空'}">
              <el-input
                  placeholder="按下 Enter 键搜索"
                  type="search"
                  icon="search"
                  v-model="form.productName"
                  :on-icon-click="search"
                  @keyup.native.enter="search">
              </el-input>
            </el-form-item>
            <div class="sub-title">选择需要查询的城市</div>
            <el-radio-group v-model="form.city" fill="#1D8CE0" text-color="#fff">
              <el-radio-button v-for="(item, index) in cities" :label="index" :key="index">{{index}}
              </el-radio-button>
            </el-radio-group>
          </el-form>
        </el-card>
      </el-col>
      <el-col :span="14" class="search-box">
        <search-history :historyIn="historyIn" @clicked="search"></search-history>
      </el-col>
    </el-row>
    <el-row v-if="localInfo.length" class="search-box render-cell">
      <search-local-info :localInfo="localInfo"></search-local-info>
    </el-row>
    <el-row v-loading="loadStateSearch" element-loading-text="正在抓取实时信息, 第一次加载耗时较久" class="render-cell">
      <real-time-result
          :results="results"
      ></real-time-result>
    </el-row>
    <el-row v-loading="loadStatePrevious" class="render-cell">
      <history-stat :history="previouslyResults" v-if="previouslyResults.length"></history-stat>
    </el-row>
  </div>
</template>
<script>
  import SearchHistory from './SearchHistory.vue'
  import RealTimeResult from './RealTimeResult.vue'
  import historyStat from './historyStat.vue'
  import SearchLocalInfo from './SearchLocalInfo.vue'

  export default {
    name: 'SearchByName',
    components: {
      SearchLocalInfo,
      SearchHistory,
      RealTimeResult,
      historyStat
    },
    data () {
      return {
        cities: [],
        historyIn: '',
        form: {
          productName: '',
          city: ''
        },
        results: {
          Jddj: [],
          Tm: [],
          Tb: [],
          Sdg: []
        },
        previouslyResults: [],
        localInfo: [],
        loadStateSearch: false,
        loadStatePrevious: false
      }
    },
    mounted () {
      this.$http.get('/queryer/Setting/get', {
        params: {
          key: 'city'
        }
      }).then(data => {
        this.cities = data.body
        this.form.city = Object.keys(this.cities)[0]
      })
    },
    methods: {
      search (pushHistory) {
        this.loadStateSearch = true
        this.loadStatePrevious = true
        this.results = {
          Jddj: [],
          Tm: [],
          Tb: [],
          Sdg: []
        }
        this.localInfo = []
        this.previouslyResults = []
        if (typeof pushHistory === 'string') {
          this.form.productName = pushHistory
        }
        this.historyIn = this.form.productName
        let city = this.form.city
        // 搜索实时信息
        this.$http.post(`/queryer/Jdquery/search`, {
          productName: this.form.productName,
          city: city
        }).then(data => {
          this.loadStateSearch = false
          this.$notify({
            title: '搜索状态',
            message: '搜索成功',
            type: 'success'
          })
          if (data.body[0] === null) {
            this.result = []
            return
          }
          data.body.forEach(val => {
            this.results.Jddj.push(val)
          })
        }).catch(data => {
          this.loadStateSearch = false
          this.$notify({
            title: '搜索失败',
            message: data.body.result,
            type: 'error'
          })
        })
        // 搜索历史信息
        this.$http.get('/queryer/index/List', {
          params: {
            productName: this.form.productName,
            city: city
          }
        }).then(data => {
          this.loadStatePrevious = false
          data.body.forEach(val => {
            this.previouslyResults.push(val)
          })
        })
        // 搜索本地商品信息
        this.$http.get('/queryer/index/LocalInfo', {
          params: {
            productName: this.form.productName
          }
        }).then(data => {
          this.localInfo = data.body
        })
      }
    }
  }
</script>
<style type="text/scss" lang='scss'>
  .search-box {
    margin: 15px;
  }

  .el-collapse-item__content {
    position: relative;
  }

  .local-image {
    position: absolute;
    top: 5px;
    right: 15px;
    img {
      right: 5%;
      border-radius: 4px;
      box-shadow: 0 2px 3px rgba(0, 0, 0, 0.2);
      width: 130px;
    }
  }
</style>

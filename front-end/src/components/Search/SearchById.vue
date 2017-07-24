<template>
  <div>
    <el-row class="search-box">
      <el-col>
        <el-card class="box-card">
          <el-form :model="form">
            <el-form-item
                label="关键词"
                prop="productName"
                :rules="{ required: true, message: '关键词不能为空'}">
              <el-autocomplete
                  type="search"
                  icon="search"
                  placeholder="按下 Enter 键搜索"
                  style="display: block"
                  v-model="form.productName"
                  :fetch-suggestions="querySearch"
                  :trigger-on-focus="false"
                  @select="handleSelect"
              ></el-autocomplete>
            </el-form-item>
            <div class="sub-title">选择需要查询的城市</div>
            <el-radio-group v-model="form.city" fill="#1D8CE0" text-color="#fff">
              <el-radio-button v-for="(item, index) in cities" :label="index" :key="index">{{index}}
              </el-radio-button>
            </el-radio-group>
          </el-form>
        </el-card>
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
    <el-row v-loading="loadStatePrevious" class="render-cell" v-if="previouslyResults.length">
      <history-stat :history="previouslyResults"></history-stat>
    </el-row>
  </div>
</template>
<script>
  import SearchHistory from './SearchHistory.vue'
  import SearchLocalInfo from './SearchLocalInfo.vue'
  import RealTimeResult from './RealTimeResult.vue'
  import historyStat from './historyStat.vue'

  export default {
    name: 'SeachById',
    components: {
      SearchHistory,
      SearchLocalInfo,
      RealTimeResult,
      historyStat
    },
    data () {
      return {
        cities: [],
        polynomial: 3,
        timeout: '',
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
      this.$emit('routerInit', '2')
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
      changeKeyWords (keyWords) {
        this.form.productName = keyWords
      },
      handleSelect (select) {
        this.search(select.id)
      },
      querySearch (queryString, cb) {
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          this.$http.get('/queryer/index/suggest', {
            params: {
              productName: queryString
            }
          }).then(data => {
            let suggests = []
            data.body.forEach(val => {
              suggests.push({
                value: val.goods_name,
                id: val.id
              })
            })
            console.log(cb)
            cb(suggests)
          })
        }, 500)
      },
      search (productId) {
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
        // 搜索实时信息
        this.$http.post(`/queryer/Jdquery/search`, {
          productId: productId,
          city: this.form.city
        }).then(data => {
          this.loadStateSearch = false
          this.$notify({
            title: `搜索状态`,
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
        })
        // 搜索历史信息
        this.$http.get('/queryer/index/List', {
          params: {
            productId: productId,
            city: this.form.city
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
            productId: productId
          }
        }).then(data => {
          this.localInfo = data.body
        })
      }
    }
  }
</script>
<style type='css/scss' lang='scss'>
  .search-box {
    margin: 15px;
  }
</style>

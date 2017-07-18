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
                  placeholder="请输入关键字"
                  type="search"
                  icon="search"
                  v-model="form.productName"
                  :on-icon-click="search"
                  @keyup.native.enter="search">
              </el-input>
            </el-form-item>
            <div class="sub-title">选择需要查询的城市</div>
            <el-checkbox-group v-model="form.city" fill="#1D8CE0" text-color="#fff" :min="1" :max="4">
              <el-checkbox-button v-for="(item, index) in cities" :label="index" :key="index">{{index}}
              </el-checkbox-button>
            </el-checkbox-group>
          </el-form>
        </el-card>
      </el-col>
      <el-col :span="14" class="search-box">
        <search-history :history="history" @clicked="search"></search-history>
      </el-col>
    </el-row>
    <el-row v-if="localInfo.length" class="search-box">
      <el-col>
        <el-card class="box-card">
          <div class="sub-title">
            星链商品信息
          </div>
          <el-collapse>
            <el-collapse-item v-for="product in localInfo" :key="product" :title="product.goods_name">
              <p>
                <span class="sub-title">
                价格:
                </span>
                <span>
                {{product.price}} (和乐) {{product.weixin_shop_price}} (微信商城)
                </span>
              </p>
              <p>
                <span class="sub-title">
                销量:
                </span>
                <span>
                {{product.goods_salenum}}
                </span>
              </p>
              <p>
                <span class="sub-title">
                库存:
              </span>
                <span>
                {{product.goods_inventory_original}}
              </span>
              </p>
            </el-collapse-item>
          </el-collapse>
        </el-card>
      </el-col>
    </el-row>
    <el-row v-loading="loadState">
      <real-time-result
          :results="results"
          :showMoreState="showMoreState"
          :ShowMore="ShowMore"
      ></real-time-result>
    </el-row>
    <el-row v-loading="loadStatePrevious">
      <el-card class="search-box box-card">
        <div class="sub-title">
          历史数据
        </div>
        <history-stat :history="previouslyResults"></history-stat>
      </el-card>
    </el-row>
  </div>
</template>
<script>
  import SearchHistory from './SearchHistory.vue'
  import RealTimeResult from './RealTimeResult.vue'
  import historyStat from './historyStat.vue'
  export default {
    name: 'SearchByName',
    components: {
      SearchHistory,
      RealTimeResult,
      historyStat
    },
    data () {
      return {
        cities: [],
        history: [],
        form: {
          productName: '',
          city: []
        },
        results: [],
        previouslyResults: [],
        localInfo: [],
        showMoreState: false,
        ShowMore: false,
        loadState: false,
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
        this.form.city.push(Object.keys(this.cities)[0])
      })
    },
    methods: {
      search (pushHistory) {
        this.loadStateSearch = true
        this.loadStatePrevious = true
        if (pushHistory instanceof Event) {
          this.history.push(this.form.productName)
        } else {
          this.form.productName = pushHistory
        }
        this.form.city.forEach((city, index) => {
          // 搜索实时信息
          this.$http.post(`/queryer/Jdquery/search`, {
            productName: this.form.productName,
            city: city
          }).then(data => {
            this.loadState = false
            if (data.body[0] === null) {
              this.result = []
              return
            }
            data.body.forEach(val => {
              this.results.push(val)
              this.ShowMore = true
            })
          })
          // 搜索历史信息
          this.$http.get('/queryer/Jdquery/List', {
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
          this.$http.get('/queryer/Jdquery/LocalInfo', {
            params: {
              productName: this.form.productName
            }
          }).then(data => {
            this.localInfo = data.body
          })
        })
      }
    }
  }
</script>
<style type="text/scss" lang='scss'>
  .search-box {
    margin: 15px;
  }

</style>

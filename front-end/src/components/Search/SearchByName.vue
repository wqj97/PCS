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
    <el-row v-loading="loadState">
      <el-card class="search-box box-card real-time-result">
        <div class="sub-title">
          实时数据 (每隔两小时采集一次)
        </div>
        <transition-group name="el-fade-in-linear">
          <el-col :span="24" v-for="(store, index) in results" :key="index" class="search-result">
            <el-card>
              <div class="store-info">
                <div class="left-pic">
                  <img :src="store.store_pic" class="image">
                </div>
                <div class="right-info">
                  <el-row>
                    <el-col :span="3" :offset="6">
                      <span class="sub-title">店名:</span>
                    </el-col>
                    <el-col :span="8">
                      {{store.store_name}}
                      <span class="store-city">( {{store.city}} )</span>
                    </el-col>
                  </el-row>
                  <template v-for="info in store.extraInfo">
                    <el-row>
                      <template v-for="(val, key) in info">
                        <el-col :span="3" :offset="6">
                          <span class="sub-title">{{key}}:</span>
                        </el-col>
                        <el-col :span="8">
                          {{val}}
                        </el-col>
                      </template>
                    </el-row>
                  </template>
                </div>
              </div>
              <div class="products">
                <el-table
                    :data="store.products"
                    style="width: 100%">
                  <el-table-column
                      prop="product_name"
                      label="商品名">
                  </el-table-column>
                  <el-table-column
                      prop="product_price"
                      label="商品价格 (元)">
                  </el-table-column>
                </el-table>
              </div>
            </el-card>
          </el-col>
        </transition-group>
      </el-card>
      <div class="block-control" @click="showMore" @mouseover="showMoreState = true"
           @mouseout="showMoreState = false"
           v-if="ShowMore">
        <i class="el-icon-caret-bottom"></i>
        <transition name="el-zoom-in-center">
          <span v-show="showMoreState">显示更多</span>
        </transition>
      </div>
    </el-row>
    <el-row v-loading="loadStatePrevious">
      <el-card class="search-box box-card">
        <div class="sub-title">
          历史数据
        </div>

      </el-card>
    </el-row>
  </div>
</template>
<script>
  import SearchHistory from './SearchHistory.vue'
  export default {
    name: 'SearchByName',
    components: {
      SearchHistory
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
      showMore () {
        window.document.querySelector('.real-time-result').className += ' show-full'
        this.ShowMore = false
      },
      search (pushHistory) {
        this.loadStateSearch = true
        this.loadStatePrevious = true
        if (pushHistory instanceof Event) {
          this.history.push(this.form.productName)
        } else {
          this.form.productName = pushHistory
        }
        this.form.city.forEach((city, index) => {
          this.$http.post(`/queryer/Jdquery/search`, {
            productName: this.form.productName,
            city: city
          }).then(data => {
            this.loadState = false
            data.body.forEach(val => {
              this.results.push(val)
              this.ShowMore = true
            })
          })
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
        })
      }
    }
  }
</script>
<style type="text/scss" lang='scss' scoped>
  .search-box {
    margin: 15px;
  }

  .image {
    width: 100%;
    border-radius: 3px;
    box-shadow: 0 3px 3px rgba(0, 0, 0, 0.4);
  }

  .store-info {
    display: flex;
    margin: 0 0 10px 0;
    .left-pic {
      width: 20%;
    }
    .right-info {
      width: 80%;
      font-size: 14px;
    }
    .el-row {
      margin: 5px 0;
      .el-col {
        text-align: right;
      }
    }
  }

  .search-result {
    margin: 15px 0;
    box-sizing: content-box;
  }

  .real-time-result {
    max-height: 500px;
    overflow: scroll;
    position: relative;
    &::-webkit-scrollbar {
      width: 5px;
      height: 0;
      background: transparent;
    }
    &::-webkit-scrollbar-thumb {
      background: #333;
      border-radius: 10px;
    }
  }

  .block-control {
    height: 36px;
    margin: 16px;
    position: relative;
    bottom: 51px;
    border-radius: 0 0 4px 4px;
    box-sizing: border-box;
    background-color: #fff;
    text-align: center;
    color: #d3dce6;
    cursor: pointer;
    z-index: 999;
    i {
      margin-top: 7px;
    }
  }

  .show-full {
    max-height: inherit;
  }
</style>

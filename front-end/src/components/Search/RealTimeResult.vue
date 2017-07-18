<template>
  <el-row>
    <el-card class="search-box box-card real-time-result">
      <div class="sub-title">
        实时数据 (每隔两小时采集一次)
      </div>
      <transition-group name="el-fade-in-linear">
        <el-col :span="24" v-for="(store, index) in results" :key="index" class="search-result"
                v-if="results.length != 0">
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
        <el-col v-else>
          <div class="sub-title">
            暂无数据
          </div>
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
</template>
<script>
  export default {
    name: 'RealTimeResult',
    props: {
      results: {
        required: true,
        type: Array
      },
      showMoreState: {
        default: false
      },
      ShowMore: {
        default: false
      }
    },
    methods: {
      showMore () {
        window.document.querySelector('.real-time-result').className += ' show-full'
        this.ShowMore = false
      }
    }
  }
</script>
<style type='text/scss' lang='scss' scoped>
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

  .show-full {
    max-height: inherit;
  }

  .image {
    width: 100%;
    border-radius: 3px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.4);
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
</style>

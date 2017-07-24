<template>
  <el-row>
    <el-card class="search-box box-card real-time-result">
      <div class="sub-title">
        实时数据 (每隔两小时采集一次)
      </div>
      <transition name="el-fade-in-linear">
        <el-col :span="24" class="search-result">
          <el-card>
            <div class="products">
              <el-table
                  :data="products"
                  style="width: 100%">
                <el-table-column
                    label="店名"
                    prop="store_info">
                  <template scope="store_info">
                    <el-tooltip placement="right">
                      <div slot="content">所在城市: {{store_info.row.store_info.city}}
                        <template v-for="extraInfo in store_info.row.store_info.extraInfo">
                          <br/>
                          <template v-for="(val, key) in extraInfo">
                            {{key}}: {{val}}
                          </template>
                        </template>
                      </div>
                      <span class="store-name" @click="openStoreInfo(store_info.row.store_info)">{{store_info.row
                        .store_info.store_name}} ({{store_info.row.dataSource}})</span>
                    </el-tooltip>
                  </template>
                </el-table-column>
                <el-table-column
                    prop="product_name"
                    label="商品名">
                  <template scope="scope">
                    <a :href="scope.row.product_url" class="store-name">{{scope.row.product_name}}</a>
                  </template>
                </el-table-column>
                <el-table-column
                    prop="product_price"
                    label="商品价格 (元)">
                </el-table-column>
              </el-table>
            </div>
          </el-card>
        </el-col>
      </transition>
    </el-card>
    <div class="block-control" @click="showMore" @mouseover="showMoreState = true"
         @mouseout="showMoreState = false" v-if="!opened">
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
        type: Object
      }
    },
    data () {
      return {
        showMoreState: false,
        opened: false
      }
    },
    methods: {
      showMore () {
        window.document.querySelector('.real-time-result').className += ' show-full'
        this.opened = true
      },
      openStoreInfo (storeInfo) {
        const h = this.$createElement
        let extra = []
        storeInfo.extraInfo.forEach((each) => {
          extra.push(h('p', null, [h('span', {
            'class': 'sub-title'
          }, `${Object.keys(each)[0]}: `),
            h('span', null, Object.values(each)[0])]))
        })
        this.$msgbox({
          title: storeInfo.store_name,
          message: h('div', null, [
            h('p', null, [
              h('span', {
                'class': 'sub-title'
              }, '所在城市: '),
              h('span', null, storeInfo.city)
            ]),
            h('p', {'class': 'store-image'}, [
              h('img', {
                attrs: {
                  src: storeInfo.store_pic
                }
              })
            ]),
            extra
          ])
        })
      }
    },
    computed: {
      products () {
        let products = []
        this.results.Jddj.forEach(store => {
          store.products.forEach(product => {
            products.push({
              product_name: product.product_name,
              product_price: product.product_price,
              product_url: product.product_url,
              store_info: store,
              dataSource: '京东到家'
            })
          })
        })
        return products
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

  .store-name {
    color: #1D8CE0;
    cursor: pointer;
  }

  .store-image {
    position: absolute;
    right: 20px;
    top: 15px;
    img {
      width: 100px;
      box-shadow: 0 2px 3px rgba(0, 0, 0, 0.2);
      border-radius: 2px;
    }
  }
</style>

<template>
  <div>
    <el-table
        :data="analyzeResult"
        style="width: 100%">
      <el-table-column
          prop="productName"
          label="产品名">
      </el-table-column>
      <el-table-column
          prop="price"
          label="星链生活价格">
      </el-table-column>
      <el-table-column
          label="京东到家价格(多项平均)(点击查看详情)">
        <template scope="Jd">
          <div class="price-click" @click="showDetail(Jd.row.Jd_info)" :class="hightOrLow(Jd.row)">
            {{Jd.row.Jd_info | average}}
          </div>
        </template>
      </el-table-column>
      <el-table-column
          prop="Tbk_info"
          label="淘宝客价格(多项平均)(点击查看详情)">
        <template scope="Tbk">
          <div class="price-click" @click="showDetail(Tbk.row.Tbk_info)" :class="hightOrLow(Tbk.row)">
            {{Tbk.row.Tbk_info | average}}
          </div>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog title="收货地址" :visible.sync="dialogTableVisible">
      <el-table :data="detailData">
        <el-table-column property="product_name" label="产品名"></el-table-column>
        <el-table-column property="product_price" label="价格"></el-table-column>
        <el-table-column label="原始链接">
          <template scope="detail">
            <a :href="detail.row.product_url" target="_blank">原始链接</a>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>
<script>
  export default {
    name: 'analyzeViewer',
    data () {
      return {
        analyzeResult: [],
        analyzeHidden: [],
        dialogTableVisible: false,
        detailData: []
      }
    },
    mounted () {
      this.$http.get(`/analyze/index/getData?Id=${this.$route.params.Id}`).then(data => {
        data = JSON.parse(data.body)
        let hidden = []
        let display = []
        data = Object.keys(data).forEach(key => {
          let tmp = data[key]
          let outObj = {
            productName: key,
            price: tmp.price,
            Jd_info: tmp.Jddj_products,
            Tbk_info: tmp.Tbk_products
          }
          if (tmp.Jddj_products.length === 0 && tmp.Tbk_products.length === 0) {
            hidden.push(outObj)
          } else {
            display.push(outObj)
          }
        })
        display.sort((val1, val2) => {
          return (val2.Jd_info.length + val2.Tbk_info.length) - (val1.Jd_info.length + val1.Tbk_info.length)
        })
        this.analyzeHidden = hidden
        this.analyzeResult = display
      })
    },
    methods: {
      showDetail (data) {
        this.dialogTableVisible = true
        this.detailData = data
      },
      hightOrLow (data) {
        let averageNum = 0
        data.Tbk_info.map(val => {
          return Number(val.product_price)
        }).forEach(price => {
          averageNum += price
        })
        averageNum /= data.Tbk_info.length
        if (Number(averageNum) === Number(data.price)) {
          return ''
        }
        if (averageNum > data.price) {
          return 'hight'
        } else {
          return 'low'
        }
      }
    },
    filters: {
      average (data) {
        if (data.length === 0) return '--'
        let averageNum = 0
        data.map(val => {
          return Number(val.product_price)
        }).forEach(price => {
          averageNum += price
        })
        return (averageNum / data.length).toFixed(2)
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>
  .hight {
    color: #EE3030;
  }

  .low {
    color: #8CC152;
  }

  .price-click {
    cursor: pointer;
  }
</style>

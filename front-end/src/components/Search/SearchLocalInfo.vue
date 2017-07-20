<template>
  <el-col>
    <el-card class="box-card">
      <div class="sub-title">
        星链商品信息
      </div>
      <el-collapse class="local-collapse">
        <el-collapse-item v-for="(product, index) in localInfo" :key="index" :title="product.goods_name">
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
                {{product.goods_inventory_original ? product.goods_inventory_original : 0}}
              </span>
          </p>
          <div class="local-image">
            <img :src="product.logourl" alt="">
          </div>
        </el-collapse-item>
      </el-collapse>
    </el-card>
  </el-col>
</template>
<script>
  export default {
    name: 'SearchLocalInfo',
    props: {
      localInfo: {
        type: Array
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>

</style>

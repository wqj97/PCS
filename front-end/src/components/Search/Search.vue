<template>
  <div>
    <el-col :span="4" class="left-menu">
      <el-menu :default-active="actived" class="el-menu-vertical-demo">
        <el-menu-item
            index="1"><i class="el-icon-menu"></i>
          <router-link to="/Search/byName">关键字查询</router-link>
        </el-menu-item>
        <el-menu-item
            index="2"><i class="el-icon-date"></i>
          <router-link to="/Search/byId">按商品查询</router-link>
        </el-menu-item>
      </el-menu>
    </el-col>
    <el-col :span="20">
      <router-view @routerInit="routerInit"></router-view>
    </el-col>
  </div>
</template>
<script>
  export default {
    name: 'Search',
    mounted () {
      this.$store.commit('updateHomeIndex', {homeIndex: '1'})
    },
    data () {
      return {
        actived: '1'
      }
    },
    methods: {
      routerInit (index) {
        this.actived = index
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>
  a {
    display: inline-block;
    width: 100%;
  }
</style>

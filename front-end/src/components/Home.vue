<template>
  <div>
    <el-menu :default-active="activeIndex2" class="el-menu-demo" mode="horizontal" @select="handleSelect">
      <el-menu-item index="1">控制中心</el-menu-item>
      <el-menu-item index="2">价格查询</el-menu-item>
      <el-menu-item index="3">变动预警</el-menu-item>
      <el-menu-item index="4">活动监听</el-menu-item>
      <el-menu-item index="5">系统设置</el-menu-item>
    </el-menu>
  </div>
</template>
<script>
  export default {
    name: 'Home'
  }
</script>
<style type='css/scss' lang='scss'>
</style>

<template>
  <div id="app">
    <el-menu :default-active="homeIndex" class="home-menu" mode="horizontal">
      <el-menu-item index="0">
        <router-link to="Console">控制中心</router-link>
      </el-menu-item>
      <el-menu-item index="1">
        <router-link to="/Search/byName">价格查询</router-link>
      </el-menu-item>
      <el-menu-item index="2">变动预警</el-menu-item>
      <el-menu-item index="3">活动监听</el-menu-item>
      <el-menu-item index="4">系统设置</el-menu-item>
    </el-menu>
    <transition name="el-fade-in">
      <router-view class="main"></router-view>
    </transition>

  </div>
</template>

<script>
  export default {
    name: 'app',
    computed: {
      homeIndex () {
        return this.$store.state.homeIndex
      }
    }
  }
</script>

<style lang="scss" type="text/scss">
  html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
  }

  .home-menu {
    a {
      text-decoration: none;
      display: block;
    }
  }
  a{
    color: inherit;
    text-decoration: none;
  }
  .el-fade-in-leave-active {
    position: absolute;
  }

</style>

<template>
  <div id="app">
    <el-menu :default-active="homeIndex" class="home-menu" mode="horizontal">
      <el-menu-item index="0">
        <router-link to="Console">控制中心</router-link>
      </el-menu-item>
      <el-menu-item index="1">
        <router-link to="/Search/byName">价格查询</router-link>
      </el-menu-item>
      <el-menu-item index="2">行情概览</el-menu-item>
      <el-menu-item index="3">活动监听</el-menu-item>
      <el-menu-item index="4"><router-link to="/Setting">系统设置</router-link></el-menu-item>
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

  a {
    color: inherit;
    text-decoration: none;
  }

  .el-fade-in-leave-active {
    position: absolute;
  }

  .sub-title {
    margin: 10px 0;
    font-size: 14px;
    color: #8492a6;
  }

  .render-cell {
    transform: translateZ(0);
  }

  .el-loading-mask {
    user-select: none;
    -webkit-backdrop-filter: blur(10px);
  }
</style>

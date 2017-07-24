<template>
  <el-row>
    <el-row>

    </el-row>
  </el-row>
</template>
<script>
  export default {
    name: 'Analyze',
    mounted () {
      this.$store.commit('updateHomeIndex', {homeIndex: '2'})
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>

</style>

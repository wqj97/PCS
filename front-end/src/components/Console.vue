<template>
  <div>
    <h1>Console</h1>
  </div>
</template>
<script>
  export default {
    name: 'Console',
    mounted () {
      this.$store.commit('updateHomeIndex', {homeIndex: '0'})
    }
  }
</script>
<style type='css/scss' lang='scss'>

</style>

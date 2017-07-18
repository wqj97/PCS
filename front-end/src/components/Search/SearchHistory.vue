<template>
  <div>
    <el-card class="box-card">
      <div class="sub-title">从历史记录查询</div>
      <el-tag
          v-for="tag in history"
          :key="tag"
          type="gray"
          :closable="true"
          @close="handleClose(tag)">
        <span @click="handleClick(tag)" class="tag-span">{{tag}}</span>
      </el-tag>
    </el-card>
  </div>
</template>
<script>
  export default {
    name: 'SearchHistory',
    props: {
      history: {
        type: Array,
        require: true
      }
    },
    mounted () {
      let storage = JSON.parse(this.$localStorage.get('search_history_by_name'))
      if (storage === null) {
        storage = []
      }
      if (storage.length !== 0) {
        storage.forEach(val => {
          this.history.unshift(val)
        })
      }
    },
    methods: {
      handleClose (tag) {
        this.history.splice(this.history.indexOf(tag), 1)
      },
      handleClick (tag) {
        this.$emit('clicked', tag)
      }
    },
    watch: {
      history () {
        this.$localStorage.set('search_history_by_name', JSON.stringify(this.history))
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>
  .tag-span {
    cursor: pointer;
    user-select: none;
  }
</style>

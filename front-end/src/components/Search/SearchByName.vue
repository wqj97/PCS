<template>
  <div>
    <el-row>
      <el-col :span="8" class="search-box">
        <el-card class="box-card">
          <el-form :model="form">
            <el-form-item
                label="关键词"
                prop="productName"
                :rules="{ required: true, message: '关键词不能为空'}">
              <el-input
                  placeholder="请输入关键字"
                  type="search"
                  icon="search"
                  v-model="form.productName"
                  :on-icon-click="search"
                  @keyup.native.enter="search">
              </el-input>
            </el-form-item>
            <div class="sub-title">选择需要查询的城市</div>
            <el-checkbox-group v-model="form.city" fill="#1D8CE0" text-color="#fff" :min="1" :max="4">
              <el-checkbox-button v-for="(item, index) in cities" :label="index" :key="index">{{index}}
              </el-checkbox-button>
            </el-checkbox-group>
          </el-form>
        </el-card>
      </el-col>
      <el-col :span="14" class="search-box">
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
      </el-col>
    </el-row>
    <el-row>
      <el-card class="search-box box-card">
        <div class="sub-title">
          两小时最新数据
        </div>
      </el-card>
    </el-row>
    <el-row>
      <el-card class="search-box box-card">
        <div class="sub-title">
          历史数据
        </div>
      </el-card>
    </el-row>
  </div>
</template>
<script>
  export default {
    name: '',
    data () {
      return {
        cities: [],
        history: [],
        form: {
          productName: '',
          city: []
        },
        results: []
      }
    },
    mounted () {
      this.$http.get('/queryer/Setting/get', {
        params: {
          key: 'city'
        }
      }).then(data => {
        this.cities = data.body
        this.form.city.push(Object.keys(this.cities)[0])
      })
    },
    methods: {
      handleClose (tag) {
        this.history.splice(this.history.indexOf(tag), 1)
      },
      handleClick (tag) {
        this.form.productName = tag
        this.search(false)
      },
      search (pushHistory = true) {
        if (pushHistory) {
          this.history.push(this.form.productName)
        }
        this.form.city.forEach((city, index) => {
          this.$http.post(`/queryer/Jdquery/search`, {
            productName: this.form.productName,
            city: city
          }).then(data => {
            this.results.push(data.body)
          })
        })
      }
    }
  }
</script>
<style type='css/scss' lang='scss' scoped>
  .search-box {
    margin: 15px;
  }

  .sub-title {
    margin: 10px 0;
    font-size: 14px;
    color: #8492a6;
  }
  .tag-span{
    cursor: pointer;
    user-select: none;
  }
</style>

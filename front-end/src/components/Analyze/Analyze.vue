<template xmlns:v-popover="http://www.w3.org/1999/xhtml">
  <el-row>
    <el-row>
      <el-col :span="6">
        <el-card class="box-card">
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              总销量:
            </el-col>
            <el-col :span="14">
              12000
            </el-col>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              本月销量:
            </el-col>
            <el-col :span="14">
              4800
              <el-badge class="badge-right" value="+2400"></el-badge>
            </el-col>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              本周销量:
            </el-col>
            <el-col :span="14">
              1200
              <el-badge class="badge-right badge-minus" value="-80"></el-badge>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="box-card">
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              商品品类:
            </el-col>
            <el-col :span="14">
              3287
              <el-badge class="badge-right" value="+20"></el-badge>
            </el-col>
          </el-row>
          <el-row class="product-info-row" type="flex">
            <el-col :span="24" style="justify-content: center;">
              <el-button type="primary" size="large" style="margin-top: 15px">启动大规模分析</el-button>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card class="box-card">
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              上次分析日期:
            </el-col>
            <el-col :span="17" class="alight-right">
              {{lastAnalyzeData.lastAnalyzeDate}}
            </el-col>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              上次分析耗时:
            </el-col>
            <el-col :span="17" class="alight-right">
              {{lastAnalyzeData.timeCost}}
            </el-col>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              上次分析覆盖商品:
            </el-col>
            <el-col :span="17" class="alight-right">
              {{lastAnalyzeData.coverageCount}}
            </el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
    <el-row>
      <el-card class="box-card">
        <div class="main-title">
          <el-popover
              ref="popover1"
              placement="top-start"
              title="竞争力"
              width="200"
              trigger="hover"
              content="竞争力计算公式: 我们有的商品对方没有+3分, 同款商品我们价格有优势+2分, 反之-2分">
          </el-popover>
          总体分析 <i class="el-icon-information" v-popover:popover1></i>
        </div>
        <el-row>
          <el-col v-for="(analyze, index) in analyzeDataList" :key="index" :span="8">
            <el-card class="box-card analyze-result">
              <router-link :to="{name: 'AnalyzeViewer', params: {Id: analyze.Id}}"><span>{{analyze.A_coverage}}条结果</span>{{analyze.A_date}}</router-link>
            </el-card>
          </el-col>
        </el-row>
      </el-card>
    </el-row>
    <el-row>
      <el-col :span="12">
        <el-card class="box-card">
          <el-row>
            <div class="main-title">
              对京东到家竞争力: <span class="decimal">{{JddjScore}}</span>
            </div>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              商品品类竞争力:
            </el-col>
            <el-col :span="17">
              {{this.lastAnalyzeData.JddjScore.productScore}}<el-badge class="badge-right" value="+80"></el-badge>
            </el-col>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              商品价格竞争力:
            </el-col>
            <el-col :span="17">
              {{this.lastAnalyzeData.JddjScore.priceScore}}<el-badge class="badge-right badge-minus" value="-120"></el-badge>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card class="box-card">
          <el-row>
            <div class="main-title">
              对淘宝客竞争力: -100
            </div>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              商品品类竞争力:
            </el-col>
            <el-col :span="17">
              100<el-badge class="badge-right" value="+80"></el-badge>
            </el-col>
          </el-row>
          <el-row class="product-info-row">
            <el-col class="sub-title" :span="7">
              商品价格竞争力:
            </el-col>
            <el-col :span="17">
              -200<el-badge class="badge-right badge-minus" value="-120"></el-badge>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
  </el-row>
</template>
<script>
  export default {
    name: 'Analyze',
    data () {
      return {
        lastAnalyzeData: {
          JddjScore: {},
          TbkScore: {},
          lastAnalyzeDate: '',
          coverageCount: 0,
          timeCost: 0
        },
        analyzeDataList: []
      }
    },
    mounted () {
      this.$store.commit('updateHomeIndex', {homeIndex: '0'})
      this.$http.get('/analyze').then(data => {
        data = data.body
        let analyzeInfo = data.analyzeInfo
        this.lastAnalyzeData.JddjScore = JSON.parse(analyzeInfo.A_Jddj_score)
        this.lastAnalyzeData.lastAnalyzeDate = new Date(analyzeInfo.A_date).toLocaleString()
        this.lastAnalyzeData.timeCost = `${Math.floor(analyzeInfo.A_time_cost / 60)}分${parseInt(analyzeInfo.A_time_cost % 60)}秒`
        this.lastAnalyzeData.coverageCount = analyzeInfo.A_coverage
        this.analyzeDataList = data.analyzeDataList
      })
    },
    computed: {
      JddjScore () {
        return this.lastAnalyzeData.JddjScore.priceScore + this.lastAnalyzeData.JddjScore.productScore
      }
    }
  }
</script>
<style type='text/scss' lang='scss'>
  .box-card {
    margin: 15px;
  }

  .product-info-row {
    margin: 15px 0;
    font-size: 14px;
    .el-col {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .sub-title {
      margin: 0;
    }
    .badge-right {
      display: flex;
    }
    .alight-right {
      justify-content: flex-end;
    }
  }

  .badge-minus {
    .el-badge__content {
      background-color: #47c12b !important;
    }
  }
  .analyze-result{
    cursor: pointer;
    color: #1D8CE0;
    div{
      display: flex;
      justify-content: space-between;
      align-content:center;
    }
  }
</style>

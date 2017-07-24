<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/14
 * Time: 23:54
 */

namespace app\queryer\model;


use app\SpiderSetting;
use Requests;
use think\Db;
use think\Debug;
use think\Exception;
use think\Log;

class Spider extends SpiderSetting
{
    protected $header = [];

    protected $proxy_pool;

    function __construct ()
    {
        parent::__construct();
        $this->proxy_pool = new proxy_pool();
    }

    /**
     * 获取坐标位置和城市代码
     * @param string $city 城市名
     * @return array 1.经度 2.纬度 3.城市Id
     */
    protected function getPosition ($city = '深圳')
    {
        return $this->config['city']->$city;
    }

    /**
     * 构造一个用于 Requests 的 Option
     * @return array 1. option, 2. ip的Key, 用于在ip无效的时候, 冻结这个ip
     */
    protected function create_option ()
    {
        $random_ip = $this->proxy_pool->get_ip();
        $options = [];
        if (empty($random_ip[0])) {
            return [$options, $random_ip[1]];
        } else {
            $options['proxy'] = $random_ip[0];
            return [$options, $random_ip[1]];
        }
    }

    /**
     * 将爬取的结果存到数据库
     * @param $result array 爬取结果
     * @param $id int 本地商品Id
     * @param $type string 查询类型
     * @param $keyWord string 关键词
     * @param $city
     */
    protected function save_into_db ($result, $id = 0, $type, $keyWord = '', $city)
    {
        $encoded = json_encode($result);
        $from_id = $id != 0;
        if ($from_id) {
            $last_update_time = Db::query("SELECT unix_timestamp(P_last_update) AS 'last_update_time' FROM product_comparison WHERE P_Id = ? AND P_city = ? 
ORDER BY P_last_update DESC LIMIT 1",
                [$id, $city]);
        } else {
            $last_update_time = Db::query("SELECT unix_timestamp(P_last_update) AS 'last_update_time' FROM product_comparison WHERE P_keyWord = ? AND P_city = ? ORDER BY P_last_update DESC LIMIT 1",
                [$keyWord, $city]);
        }
        if (empty($last_update_time)) {
            $last_update_time = 0;
        } else {
            $last_update_time = $last_update_time[0]['last_update_time'];
        }
        if ($last_update_time + $this->config['auto_update_frequency'] > time()) {
            return;
        }

        switch ($type) {
            case 'Jddj':
                $type = 'P_Jddj_info';
                break;
            case 'Tm':
                $type = 'P_Tm_info';
                break;
            case 'Tb':
                $type = 'P_Tb_info';
                break;
            case 'Sdg':
                $type = 'P_Sdg_info';
                break;
            case 'Tbk':
                $type = 'P_Tm_info';
                break;
        }

        if ($from_id) {
            Db::execute("INSERT INTO `product_comparison` (P_Id,{$type},P_city) VALUES (?,?,?)", [$id,
                $encoded, $city]);
        } else {
            Db::execute("INSERT INTO `product_comparison` (P_keyWord,{$type},P_city) VALUES (?,?,?)", [$keyWord,
                $encoded, $city]);
        }
    }

    /**
     * 发送请求
     * @param $data mixed post 内容
     * @param $url string post url
     * @param $cordition_str string 判断请求成功的表达式: $response->code == 0;
     * @return mixed
     */
    protected function request ($data, $url, $cordition_str)
    {
        if ($cache = cache(sha1(json_encode($data)))) {
            return $cache;
        }
        $cordition_str = "return {$cordition_str};";
        for ($i = 0; $i < $this->config['retry_times']; $i++) {
            $option = $this->create_option();
            try {
                $response = json_decode(Requests::post($url, $this->header, $data, $option[0])->body);
                if (eval($cordition_str)) {
                    cache(sha1(json_encode($data)), $response);
                    return $response;
                } else {
                    $this->proxy_pool->freeze_ip($option[1]);
                }
            } catch (\Exception $e) {
                $this->proxy_pool->freeze_ip($option[1]);
                Log::error($e->getMessage());
            }
        }
        return false;
    }

    /**
     * 优化搜索词
     * @param $keyWords string 搜索词
     * @return string 优化后的词
     */
    protected function optimize_keyWords ($keyWords)
    {
        # 去除品牌词
        $keyWords = mbereg_replace(
            '工商银行|国家电网|CCTV|腾讯|中国移动通信|联想|中国银行|中国建设银行|中国平安|华为|中国人寿|中国石油|百度|中国石化|上汽|中国建筑|国航|新华社|阿里巴巴|中国联通|中信|中国电信|中国铁建|青岛啤酒|中国中化|中国农业银行|中国中铁|中国海油|国药控股|交通银行|宝钢|招商银行|万科|中国一汽|新华保险|美的|格力|中航工业|茅台|福田汽车|国旅|中国民生银行|京东商城|雪花|兴业银行|浦发银行|上海建工|五粮液|恒大|保利地产|东风|南航|万达|TCL|武钢|上海医药|中国东方航空|海尔|碧桂园|中兴|潍柴动力|光大银行|上海电气|长城|华夏银行|鞍钢|中粮集团|长安汽车|中集|太平洋保险|中远|长城|比亚迪|中船重工|国美电器|长虹|龙湖|蒙牛|中国黄金|北京银行|金地|金隅|东方电气|远洋地产|雅居乐|海南航空|云天化|老凤祥|中信证券|海信|首钢|广汽集团|金融街|雅戈尔|冀中能源|康美药业|鄂尔多斯|光大证券|通威|宁波银行|海通证券|双钱|洋河|燕京|银河证券|雨润|广发证券|福耀|正泰|世茂|天士力|网易|海马|海澜之家|步步高|五菱|海亮|国信证券|魏桥|力帆|招商证券|京客隆|中青旅|亨通光电|金叶珠宝|沙钢|柳工|王力|万向|宏图三胞|三一|海螺|富力地产|宇通|方正证券|国泰君安|吉利|金龙客车|同仁堂|王府井百货|云南白药|华侨城|BELLE百丽|神州数码|江淮|江铃|永辉超市|心相印|厦门国贸|物美|苏泊尔|华北|锦江国际|志高|华泰证券|广船国际|中国一拖|东方证券|安踏|石药|中国人保|红塔山|五矿集团|中华|郎酒|银联|春兰|稻花香|徐工|中国铝业|厦工|格兰仕|豪爵|哇哈哈|劲霸|红河|长城润滑油|雷沃|新浪网|北大荒|北新建材|盼盼|超大|神华|中国邮政|大唐|金驼|正大|绿城|国机|红星美凯龙|中旅|嘉陵|中煤能源|柒牌|酒泉钢铁|远东|蓝星|步阳|春天|泸州老窖|携程|顺丰|招金|沱牌|剑南春|波司登|999|奇瑞|金六福|创维|红金龙|人民电器|玲珑|恒源祥|山东航空|方太|奥康|绿地集团|中华|七匹狼|农夫山泉|华能|完达山|神威|悦达集团|方正|雅士利|黄山|福临门|首都机场|陶华碧老干妈|潮宏基|利郎|融创|白沙|圣象|汾酒|马应龙|泰康人寿|陕汽|中科曙光|传化|马钢|德力西|北极绒|全聚德|新东方|用友|衡水|崂山|舍得|群升|上海机场|明一|ZPMC|风神|雕牌|中国重汽|雷士照明|美菱|杉杉|南山|长城|小肥羊|湘电|冰山|古井贡|国贸|稻香村|中影集团|黄金叶|广发银行|奥克斯|蓝月亮|统一|太太|雪莲|朝阳|翔业|江中|杏花村|如意|新中源|雅鹿|飞科|中华保险|盾安|金茂|三元|九牧王|鲁能|联邦|淮南矿业|洽洽|海底捞|思念|银鹭|华丰|昆仑|习酒|堡狮龙|骄子|盼盼|太钢|阳光保险|万和|美心|东北制药|修正|东南|今麦郎|飞毛腿|将军|枝江|九阳|三角|晨光|江苏银行|ARROW箭牌|新郎希努尔|黄鹤楼|大阳|德尔惠|瑞星|金圣|罗蒙|博洋|盛大网络|崂山啤酒|合力|佰草集|匹克|培罗成|南极人|哈尔滨|海化|维达|方圆地板|富安娜|南京银行|娇子|报喜鸟|四川航空|天虹商场|华新水泥|忠旺|立白|侨兴|永鼎|东方金钰|铙山|天工|王守义|奔腾|红豆|SOHO中国|利群|安信|金锣|红旗渠|瑞恩|许继|欧普|大宝|民航快递|凤凰|浪潮|肯帝亚|双喜|真龙|华西村|养生堂|通化|全友|喜之郎|金帝|长丰|酒鬼|黄果树|时风|嘉丽士漆|耀华|西凤|恩威|鲁花|感康|金猴|金山|双星|元洲装饰|片仔癀|劲牌|脑白金|华夏基金|白猫|鸭鸭|金城|宋河|申通|花样年|维维|源安堂|哈德门|吉人|口子|应大|铁将军|九芝堂|依波|钻石世家|大亚|中兴|中脉|莫代尔|康奈|凯盛|恒洁|江山|水星|白塔牌|柔然|六国|先声|富贵鸟|得利斯|旺旺|肤阴洁|常柴|好孩子|拉芳|嘉实基金|白云山|中铁快运|大印象|皖牌|孚日|金丝猴|中原|金耀|南孚|汪氏|好迪|隆鑫|前海|7天连锁酒店|施大壮|钱江|羚锐|浩沙|蒂花之秀|华帝|山花|美克美家|梅林|古越龙山|飞跃|佳丽斯|皇明|金鱼|ZWZ|雅芳婷|东宝|尚豪美家|克胜|利君沙|红蜻蜓|山推|大白兔|冠生园|金苹果|龙大|中通|威龙|珠江|圣元|飞亚达|蒂爵珠宝|金太阳|露露|南街村|神奇|六神|三枪|万利达|天王|仲景|晨鸣|龙发|青春宝|汇仁|KEKE克刻|罗莱|喜盈门|甘露|狗不理|圣雪绒|金种子|盖天力|庄吉',
            '',
            $keyWords
        );
        # 去除停用词
        $keyWords = mbereg_replace(
            '啊|阿|哎|哎呀|哎哟|唉|俺|俺们|按|按照|吧|吧哒|把|罢了|被|本|本着|比|比方|比如|鄙人|彼|彼此|边|别|别的|别说|并|并且|不比|不成|不单|不但|不独|不管|不光|不过|不仅|不拘|不论|不怕|不然|不如|不特|不惟|不问|不只|朝|朝着|趁|趁着|乘|冲|除|除此之外|除非|除了|此|此间|此外|从|从而|打|待|但|但是|当|当着|到|得|的|的话|等|等等|地|第|叮咚|对|对于|多|多少|而|而况|而且|而是|而外|而言|而已|尔后|反过来|反过来说|反之|非但|非徒|否则|嘎|嘎登|该|赶|个|各|各个|各位|各种|各自|给|根据|跟|故|故此|固然|关于|管|归|果然|果真|过|哈|哈哈|呵|和|何|何处|何况|何时|嘿|哼|哼唷|呼哧|乎|哗|还是|还有|换句话说|换言之|或|或是|或者|极了|及|及其|及至|即|即便|即或|即令|即若|即使|几|几时|己|既|既然|既是|继而|加之|假如|假若|假使|鉴于|将|较|较之|叫|接着|结果|借|紧接着|进而|尽|尽管|经|经过|就|就是|就是说|据|具体地说|具体说来|开始|开外|靠|咳|可|可见|可是|可以|况且|啦|来|来着|离|例如|哩|连|连同|两者|了|临|另|另外|另一方面|论|嘛|吗|慢说|漫说|冒|么|每|每当|们|莫若|某|某个|某些|拿|哪|哪边|哪儿|哪个|哪里|哪年|哪怕|哪天|哪些|哪样|那|那边|那儿|那个|那会儿|那里|那么|那么些|那么样|那时|那些|那样|乃|乃至|呢|能|你|你们|您|宁|宁可|宁肯|宁愿|哦|呕|啪达|旁人|呸|凭|凭借|其|其次|其二|其他|其它|其一|其余|其中|起|起见|岂但|恰恰相反|前后|前者|且|然而|然后|然则|让|人家|任|任何|任凭|如|如此|如果|如何|如其|如若|如上所述|若|若非|若是|啥|上下|尚且|设若|设使|甚而|甚么|甚至|省得|时候|什么|什么样|使得|是|是的|首先|谁|谁知|顺|顺着|似的|虽|虽然|虽说|虽则|随|随着|所|所以|他|他们|他人|它|它们|她|她们|倘|倘或|倘然|倘若|倘使|腾|替|通过|同|同时|哇|万一|往|望|为|为何|为了|为什么|为着|喂|嗡嗡|我|我们|呜|呜呼|乌乎|无论|无宁|毋宁|嘻|吓|相对而言|像|向|向着|嘘|呀|焉|沿|沿着|要|要不|要不然|要不是|要么|要是|也|也罢|也好|一|一般|一旦|一方面|一来|一切|一样|一则|依|依照|矣|以|以便|以及|以免|以至|以至于|以致|抑或|因|因此|因而|因为|哟|用|由|由此可见|由于|有|有的|有关|有些|又|于|于是|于是乎|与|与此同时|与否|与其|越是|云云|哉|再说|再者|在|在下|咱|咱们|则|怎|怎么|怎么办|怎么样|怎样|咋|照|照着|者|这|这边|这儿|这个|这会儿|这就是说|这里|这么|这么点儿|这么些|这么样|这时|这些|这样|正如|吱|之|之类|之所以|之一|只是|只限|只要|只有|至|至于|诸位|着|着呢|自|自从|自个儿|自各儿|自己|自家|自身|综上所述|总的来看|总的来说|总的说来|总而言之|总之|纵|纵令|纵然|纵使|遵照|作为|兮|呃|呗|咚|咦|喏|啐|喔唷|嗬|嗯|嗳|啊哈|啊呀|啊哟|挨次|挨个|挨家挨户|挨门挨户|挨门逐户|挨着|按理|按期|按时|按说|暗地里|暗中|暗自|昂然|八成|白白|半|梆|保管|保险|饱|背地里|背靠背|倍感|倍加|本人|本身|甭|比起|比如说|比照|毕竟|必|必定|必将|必须|便|别人|并非|并肩|并没|并没有|并排|并无|勃然|不|不必|不常|不大|不得|不得不|不得了|不得已|不迭|不定|不对|不妨|不管怎样|不会|不仅仅|不仅仅是|不经意|不可开交|不可抗拒|不力|不了|不料|不满|不免|不能不|不起|不巧|不然的话|不日|不少|不胜|不时|不是|不同|不能|不要|不外|不外乎|不下|不限|不消|不已|不亦乐乎|不由得|不再|不择手段|不怎么|不曾|不知不觉|不止|不止一次|不至于|才|才能|策略地|差不多|差一点|常|常常|常言道|常言说|常言说得好|长此下去|长话短说|长期以来|长线|敞开儿|彻夜|陈年|趁便|趁机|趁热|趁势|趁早|成年|成年累月|成心|乘机|乘胜|乘势|乘隙|乘虚|诚然|迟早|充分|充其极|充其量|抽冷子|臭|初|出|出来|出去|除此|除此而外|除此以外|除开|除去|除却|除外|处处|川流不息|传|传说|传闻|串行|纯|纯粹|此后|此中|次第|匆匆|从不|从此|从此以后|从古到今|从古至今|从今以后|从宽|从来|从轻|从速|从头|从未|从无到有|从小|从新|从严|从优|从早到晚|从中|从重|凑巧|粗|存心|达旦|打从|打开天窗说亮话|大|大不了|大大|大抵|大都|大多|大凡|大概|大家|大举|大略|大面儿上|大事|大体|大体上|大约|大张旗鼓|大致|呆呆地|带|殆|待到|单|单纯|单单|但愿|弹指之间|当场|当儿|当即|当口儿|当然|当庭|当头|当下|当真|当中|倒不如|倒不如说|倒是|到处|到底|到了儿|到目前为止|到头|到头来|得起|得天独厚|的确|等到|叮当|顶多|定|动不动|动辄|陡然|都|独|独自|断然|顿时|多次|多多|多多少少|多多益善|多亏|多年来|多年前|而后|而论|而又|尔等|二话不说|二话没说|反倒|反倒是|反而|反手|反之亦然|反之则|方|方才|方能|放量|非常|非得|分期|分期分批|分头|奋勇|愤然|风雨无阻|逢|弗|甫|嘎嘎|该当|概|赶快|赶早不赶晚|敢|敢情|敢于|刚|刚才|刚好|刚巧|高低|格外|隔日|隔夜|个人|各式|更|更加|更进一步|更为|公然|共|共总|够瞧的|姑且|古来|故而|故意|固|怪|怪不得|惯常|光|光是|归根到底|归根结底|过于|毫不|毫无|毫无保留地|毫无例外|好在|何必|何尝|何妨|何苦|何乐而不为|何须|何止|很|很多|很少|轰然|后来|呼啦|忽地|忽然|互|互相|哗啦|话说|还|恍然|会|豁然|活|伙同|或多或少|或许|基本|基本上|基于|极|极大|极度|极端|极力|极其|极为|急匆匆|即将|即刻|即是说|几度|几番|几乎|几经|既...又|继之|加上|加以|间或|简而言之|简言之|简直|见|将才|将近|将要|交口|较比|较为|接连不断|接下来|皆可|截然|截至|藉以|借此|借以|届时|仅|仅仅|谨|进来|进去|近|近几年来|近来|近年来|尽管如此|尽可能|尽快|尽量|尽然|尽如人意|尽心竭力|尽心尽力|尽早|精光|经常|竟|竟然|究竟|就此|就地|就算|居然|局外|举凡|据称|据此|据实|据说|据我所知|据悉|具体来说|决不|决非|绝|绝不|绝顶|绝对|绝非|均|喀|看|看来|看起来|看上去|看样子|可好|可能|恐怕|快|快要|来不及|来得及|来讲|来看|拦腰|牢牢|老|老大|老老实实|老是|累次|累年|理当|理该|理应|历|立|立地|立刻|立马|立时|联袂|连连|连日|连日来|连声|连袂|临到|另方面|另行|另一个|路经|屡|屡次|屡次三番|屡屡|缕缕|率尔|率然|略|略加|略微|略为|论说|马上|蛮|满|没|没有|每逢|每每|每时每刻|猛然|猛然间|莫|莫不|莫非|莫如|默默地|默然|呐|那末|奈|难道|难得|难怪|难说|内|年复一年|凝神|偶而|偶尔|怕|砰|碰巧|譬如|偏偏|乒|平素|颇|迫于|扑通|其后|其实|奇|齐|起初|起来|起首|起头|起先|岂|岂非|岂止|迄|恰逢|恰好|恰恰|恰巧|恰如|恰似|千|万|千万|千万千万|切|切不可|切莫|切切|切勿|窃|亲口|亲身|亲手|亲眼|亲自|顷|顷刻|顷刻间|顷刻之间|请勿|穷年累月|取道|去|权时|全都|全力|全年|全然|全身心|然|人人|仍|仍旧|仍然|日复一日|日见|日渐|日益|日臻|如常|如此等等|如次|如今|如期|如前所述|如上|如下|汝|三番两次|三番五次|三天两头|瑟瑟|沙沙|上|上来|上去|一.|一一|一下|一个|一些|一何|一则通过|一天|一定|一时|一次|一片|一番|一直|一致|一起|一转眼|一边|一面|上升|上述|上面|下|下列|下去|下来|下面|不一|不久|不变|不可|不够|不尽|不尽然|不敢|不断|不若|不足|与其说|专门|且不说|且说|严格|严重|个别|中小|中间|丰富|为主|为什麽|为止|为此|主张|主要|举行|乃至于|之前|之后|之後|也就是说|也是|了解|争取|二来|云尔|些|亦|产生|人|人们|什麽|今|今后|今天|今年|今後|介于|从事|他是|他的|代替|以上|以下|以为|以前|以后|以外|以後|以故|以期|以来|任务|企图|伟大|似乎|但凡|何以|余外|你是|你的|使|使用|依据|依靠|便于|促进|保持|做到|傥然|儿|允许|元／吨|先不先|先后|先後|先生|全体|全部|全面|共同|具体|具有|兼之|再|再其次|再则|再有|再次|再者说|决定|准备|凡|凡是|出于|出现|分别|则甚|别处|别是|别管|前此|前进|前面|加入|加强|十分|即如|却|却不|原来|又及|及时|双方|反应|反映|取得|受到|变成|另悉|只|只当|只怕|只消|叫做|召开|各人|各地|各级|合理|同一|同样|后|后者|后面|向使|周围|呵呵|咧|唯有|啷当|喽|嗡|嘿嘿|因了|因着|在于|坚决|坚持|处在|处理|复杂|多么|多数|大力|大多数|大批|大量|失去|她是|她的|好|好的|好象|如同|如是|始而|存在|孰料|孰知|它们的|它是|它的|安全|完全|完成|实现|实际|宣布|容易|密切|对应|对待|对方|对比|小|少数|尔|尔尔|尤其|就是了|就要|属于|左右|巨大|巩固|已|已矣|已经|巴|巴巴|帮助|并不|并不是|广大|广泛|应当|应用|应该|庶乎|庶几|开展|引起|强烈|强调|归齐|当前|当地|当时|形成|彻底|彼时|往往|後来|後面|得了|得出|得到|心里|必然|必要|怎奈|怎麽|总是|总结|您们|您是|惟其|意思|愿意|成为|我是|我的|或则|或曰|战斗|所在|所幸|所有|所谓|扩大|掌握|接著|数/|整个|方便|方面|无|无法|既往|明显|明确|是不是|是以|是否|显然|显著|普通|普遍|曾|曾经|替代|最|最后|最大|最好|最後|最近|最高|有利|有力|有及|有所|有效|有时|有点|有的是|有着|有著|末##末|本地|来自|来说|构成|某某|根本|欢迎|欤|正值|正在|正巧|正常|正是|此地|此处|此时|此次|每个|每天|每年|比及|比较|没奈何|注意|深入|清楚|满足|然後|特别是|特殊|特点|犹且|犹自|现代|现在|甚且|甚或|甚至于|用来|由是|由此|目前|直到|直接|相似|相信|相反|相同|相对|相应|相当|相等|看出|看到|看看|看见|真是|真正|眨眼|矣乎|矣哉|知道|确定|种|积极|移动|突出|突然|立即|竟而|第二|类如|练习|组成|结合|继后|继续|维持|考虑|联系|能否|能够|自后|自打|至今|至若|致|般的|良好|若夫|若果|范围|莫不然|获得|行为|行动|表明|表示|要求|规定|觉得|譬喻|认为|认真|认识|许多|设或|诚如|说明|说来|说说|诸|诸如|谁人|谁料|贼死|赖以|距|转动|转变|转贴|达到|迅速|过去|过来|运用|还要|这一来|这次|这点|这种|这般|这麽|进入|进步|进行|适应|适当|适用|逐步|逐渐|通常|造成|遇到|遭到|遵循|避免|那般|那麽|部分|采取|里面|重大|重新|重要|针对|问题|防止|附近|限制|随后|随时|随著|难道说|集中|需要|非特|非独|高兴|若果',
            ' ',
            $keyWords);
        $keyWords = mbereg_replace('[0-9]+g', '', $keyWords);
        return mbereg_replace('[\(（].*?[\)）]', '', $keyWords);
    }

    /**
     * 搜索类, 作为主入口, 商品名, 商品Id 二选一
     * @param $product_name string 商品名
     * @param int $product_id 本地商品Id
     * @param string $city 城市
     */
    public function Search ($product_name, $product_id = 0, $city = '深证')
    {
    }

    /**
     * @param $response mixed request传来的序列化结果
     * @param $city
     * @return array Store类数组
     */
    protected function parse ($response, $city)
    {
        return [];
    }

    /**
     * 发送请求
     * @param string $product_name 商品名称
     * @param string $city 在城市中搜索
     * @return mixed
     */
    protected function make_request ($product_name = '', $city)
    {
        $position = $this->getPosition($city);
        $body = [];
        $data = [];
        return $this->request($data, 'url');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use stdClass;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IndexController extends Controller
{
    public function index(){
        $a = '1231';
        echo gettype($a);
        die;
        //关键词
        $keyword = "山水画";
        //url字符编码
        $keyword = urlencode($keyword);
        //拼接请求地址
        $origin_url = 'https://pic.sogou.com/napi/pc/searchList';
        $mode = 1;
        $start = 0;
        $len = 50;
        $img[][] = new stdClass();
        $flag = 0;
        for ($start;$start<=2000;$start += 50){
            $page_url = json_decode($this->get_url($origin_url,$mode,$start,$len,$keyword));
            $data = json_decode($this->get_data($page_url));
            //获取有效图片的远程地址
            foreach ($data as $k => $v){
                if( $v -> oriPicUrl != NULL){
//                    if($v->title){
//                        echo '第'.$flag.'条'.'&nbsp;&nbsp;'.$v->title;
//                    }else{
//                        echo '第'.$flag.'条'.'&nbsp;&nbsp;无title信息';
//                    }
//                    DB::table('img')->insert([
//                        'href' => $v->oriPicUrl
//                    ]);
                    $img[$flag]['href'] = $v->oriPicUrl;
                    $flag++;
                }
            }
        }
        dd($img);
    }
    public function get_url($origin_url,$mode,$start,$len,$keyword){
        $url = $origin_url.'?mode='.$mode.'&start='.$start.'&xml_len='.$len.'&query='.$keyword;
        return json_encode($url);
    }
    public function get_data($url){
        //测试guzzle
        $client = new Client();
        $res = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 20, //超时时间（秒）
        ]);
        $res->getStatusCode(); // 获得接口反馈状态码
        $body = $res->getBody(); //获得接口返回的主体对象
        $body = $body->getContents(); //获得主体内容
        $data = json_decode($body);
        return json_encode($data->data->items);
    }
    public function t($c){
        $data = DB::table('xinhua')->inRandomOrder()->select(['title','content'])->take($c)->get();
        $img = DB::table('img')->inRandomOrder()->select(['href'])->take($c)->get();
        $arr[][] = new stdClass();
        foreach ($data as $k => $v){
            $arr[$k]['title']= $v->title;
            $arr[$k]['content'] = $v->content;
            $arr[$k]['img'] = $img[$k]->href;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', '标题');
        $sheet->setCellValue('B1', '详情');
        $sheet->setCellValue('C1', '缩略图');

        $i =2;
        foreach( $arr as $k => $v ){
            $sheet->setCellValue('A'.$i, $v['title']);
            $sheet->setCellValue('B'.$i, $v['content']);
            $sheet->setCellValue('C'.$i, $v['img']);
            $i ++;
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save( storage_path() . '/student.xlsx');
        # 需要下载
        $file = storage_path() . '/student.xlsx';

        return  response() -> download($file);
    }
    public function q(){
        $s = $this->get_cn_array();
        $chunk = mb_check_encoding($s, 'utf-8') ? 3 : 2;
        $a = str_split($s, $chunk);
        shuffle($a);
        $res = array_map('join', array_chunk($a, 40));
        foreach ($res as $k => $v){
            $res[$k] = $v.'?';
        }
        $type = array('计算机','农业','音乐');
        $data = DB::table('xinhua')->inRandomOrder()->select(['title'])->take(25)->get();
        $arr[][] = new stdClass();
        foreach ($res as $k => $v){
            $key = rand(0,2);
            $arr[$k]['title']= $v;
            $arr[$k]['type'] = $type[$key];
            $arr[$k]['miaoshu'] = $data[$k]->title;
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', '标题');
        $sheet->setCellValue('B1', '分类');
        $sheet->setCellValue('C1', '描述');

        $i =2;
        foreach( $arr as $k => $v ){
            $sheet->setCellValue('A'.$i, $v['title']);
            $sheet->setCellValue('B'.$i, $v['type']);
            $sheet->setCellValue('C'.$i, $v['miaoshu']);
            $i ++;
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save( storage_path() . '/student.xlsx');
        # 需要下载
        $file = storage_path() . '/student.xlsx';

        return  response() -> download($file);
    }
    public function get_cn_array(){
        $arr = '天地玄黄宇宙洪荒日月盈昃辰宿列张寒来暑往秋收冬藏闰馀成岁律吕调阳云腾致雨露结为霜金生丽水玉出昆冈剑号巨阙珠称夜光果珍李柰菜重芥姜海咸河淡鳞潜羽翔龙师火帝鸟官人皇始制文字乃服衣裳推位让国有虞陶唐吊民伐罪周发殷汤坐朝问道垂拱平章爱育黎首臣伏戎羌遐迩壹体率宾归王鸣凤在竹白驹食场化被草木赖及万方盖此身发四大五常恭惟鞠养岂敢毁伤女慕贞洁男效才良知过必改得能莫忘罔谈彼短靡恃己长信使可覆器欲难量墨悲丝染诗赞羔羊景行维贤克念作圣德建名立形端表正空谷传声虚堂习听祸因恶积福缘善庆尺璧非宝寸阴是竞资父事君曰严与敬孝当竭力忠则尽命临深履薄夙兴温凊似兰斯馨如松之盛川流不息渊澄取映容止若思言辞安定笃初诚美慎终宜令荣业所基籍甚无竟学优登仕摄职从政存以甘棠去而益咏乐殊贵贱礼别尊卑上和下睦夫唱妇随外受傅训入奉母仪诸姑伯叔犹子比儿孔怀兄弟同气连枝交友投分切磨箴规仁慈隐恻造次弗离节义廉退颠沛匪亏性静情逸心动神疲守真志满逐物意移坚持雅操好爵自縻都邑华夏东西二京背邙面洛浮渭据泾宫殿盘郁楼观飞惊图写禽兽画彩仙灵丙舍傍启甲帐对楹肆筵设席鼓瑟吹笙升阶纳陛弁转疑星右通广内左达承明既集坟典亦聚群英杜稿钟隶漆书壁经府罗将相路侠槐卿户封八县家给千兵高冠陪辇驱毂振缨世禄侈富车驾肥轻策功茂实勒碑刻铭番溪伊尹佐时阿衡奄宅曲阜微旦孰营桓公匡合济弱扶倾绮回汉惠说感武丁俊义密勿多士实宁晋楚更霸赵魏困横假途灭虢践土会盟何遵约法韩弊烦刑起翦颇牧用军最精宣威沙漠驰誉丹青九州禹迹百郡秦并岳宗泰岱禅主云亭雁门紫塞鸡田赤城昆池碣石巨野洞庭旷远绵邈岩岫杳冥治本于农务兹稼穑叔载南亩我艺黍稷税熟贡新劝赏黜陟孟轲敦素史鱼秉直庶几中庸劳谦谨敕聆音察理鉴貌辨色贻厥嘉猷勉其祗植省躬讥诫宠增抗极殆辱近耻林皋幸即两疏见机解组谁逼索居闲处沉默寂寥求古寻论散虑逍遥欣奏累遣戚谢欢招渠荷的历园莽抽条枇杷晚翠梧桐蚤凋陈根委翳落叶飘摇游昆独运凌摩绛霄耽读玩市寓目囊箱易酋攸畏属耳垣墙具膳餐饭适口充肠饱饫烹宰饥厌糟糠亲戚故旧老少异粮妾御绩纺侍巾帷房纨扇圆洁银烛炜煌昼眠夕寐蓝笋象床弦歌酒宴接杯举觞矫手顿足悦豫且康嫡后嗣续祭祀蒸尝稽颡再拜悚惧恐惶笺牒简要顾答审详骸垢想浴执热愿凉驴骡犊特骇跃超骧诛斩贼盗捕获叛亡布射僚丸嵇琴阮啸恬笔伦纸钧巧任钓释纷利俗并皆佳妙毛施淑姿工颦妍笑年矢每催曦晖朗曜璇玑悬斡晦魄环照指薪修祜永绥吉劭矩步引领俯仰廊庙束带矜庄徘徊瞻眺孤陋寡闻愚蒙等诮谓语助者焉哉乎也';
        return $arr;
    }
    public function z($c){
        $data = DB::table('xinhua')->inRandomOrder()->select(['title','content'])->take($c)->get();
        $img = DB::table('img')->inRandomOrder()->select(['href'])->take($c)->get();
        $cate = array('计算机','编程','半导体','医疗','测试一级分类','测试二级分类','测试二级二','测试二级三1','音乐','资讯推荐');
        $arr[][] = new stdClass();
        foreach ($data as $k => $v){
            $key = rand(0,9);
            $arr[$k]['title'] = $v->title;
            $arr[$k]['content'] = $v->content;
            $arr[$k]['type'] = $cate[$key];
            $arr[$k]['img'] = $img[$k]->href;
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', '资讯标题');
        $sheet->setCellValue('B1', '详情');
        $sheet->setCellValue('C1', '行业一级/二级分类');
        $sheet->setCellValue('D1', '缩略图');

        $i =2;
        foreach( $arr as $k => $v ){
            $sheet->setCellValue('A'.$i, $v['title']);
            $sheet->setCellValue('B'.$i, $v['content']);
            $sheet->setCellValue('C'.$i, $v['type']);
            $sheet->setCellValue('D'.$i, $v['img']);
            $i ++;
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save( storage_path() . '/student.xlsx');
        # 需要下载
        $file = storage_path() . '/student.xlsx';

        return  response() -> download($file);
    }
    public function h($c){
        $data = DB::table('xinhua')->inRandomOrder()->select(['title','content'])->take($c)->get();
        $img = DB::table('img')->inRandomOrder()->select(['href'])->take($c)->get();
        $arr[][] = new stdClass();
        foreach ($data as $k => $v){
            $key = rand(0,9);
            $arr[$k]['title'] = $v->title;
            $arr[$k]['content'] = $v->content;
            $arr[$k]['img'] = $img[$k]->href;
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', '资讯标题');
        $sheet->setCellValue('B1', '详情');
        $sheet->setCellValue('C1', '缩略图');

        $i =2;
        foreach( $arr as $k => $v ){
            $sheet->setCellValue('A'.$i, $v['title']);
            $sheet->setCellValue('B'.$i, $v['content']);
            $sheet->setCellValue('C'.$i, $v['img']);
            $i ++;
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save( storage_path() . '/student.xlsx');
        # 需要下载
        $file = storage_path() . '/student.xlsx';

        return  response() -> download($file);
    }
}

<?php
    /**
     * 实现html类
     * 用于爬虫之类的支持
     */
    class TmumuHtml{
        public $domobj;
        function __construct($html = ""){
            //去掉
            $html = preg_replace('/(<!Doctype html>|<\/br>|<br>|<!doctype html>)/','',$html);
            $html = preg_replace('/(<script(.*?)>)(.|\n)*?(<\/script>)/', '', $html);
            $html = preg_replace('/(<style(.*?)>)(.|\n)*?(<\/style>)/', '', $html);
            $html = preg_replace('/<!--(.*?)-->/', '', $html);
            $html = preg_replace('/(<!--|-->)/', '', $html);
            $html = preg_replace('/<meta(.*?)>/', '', $html);
            $html = preg_replace('/<link(.*?)>/', '', $html);
            $html = preg_replace('/<img(.*?)>/', '', $html);
            // var_dump($html);die;
            // $html = preg_match('/<html[^>]*>/',$html,$match);
            $this->domobj = $this->dom($html);
        }
        public function htmltrim($html = ''){
            return rtrim(ltrim($html, '<'), '>');
        }

        //style="color:red;width:11px;"
        public  function getStyle($string){
            $arr = [];
            $tmp = new stdClass();
            //匹配style
            $style = preg_match('/style[ ]*=[ ]*"[^"]*"/', $string, $match);
            if (count($match)>0){
                $arr = explode("=", $match[0]);
                $arr[0] = trim($arr[0], ' ');
                $arr[1] = trim($arr[1], '"');
                $arr[1] = explode(";", $arr[1]);
                if (count($arr[1]) > 0) {
                    foreach($arr[1] as $sub_k=>$sub_v){
                        $arr[1][$sub_k] = explode(':',$sub_v);
                    }
                }
            }

            return $arr;
        }
        public function htmldecode(){
            echo "aa";
        }

        public function dom($html){
            $data = new stdClass();
            $cur = $data;//当前对象
            $i = 0;
            $j = 0;
            $ii = 0;
            $str = '';
            $tmp = [];
            $issub = false;
            $is_continue = false;
            $preg = '/<[^>]*>/';
            preg_match($preg, $html, $match);
            if (count($match) > 0){
                
                while(preg_match('/<[^\/]/', $html, $tmpdata)){
                    $preg = '/<[^>]*>/';//匹配<>的正则表达式
                    preg_match($preg, $html, $match);
                    preg_match('/[^<][^ |^>]*/', $match[0],  $title);//获取html标签名曾
                    $tmpdata = new stdClass();
                    
                    $tmpdata->name = $title[0];
                    $tmpdata->style = new stdClass();
                    $tmpdata->sub = new stdClass();
                    if (!$issub){//同级元素处理
                        $data->$i = $tmpdata;
                        $cur = $data->$i;
                        $i++;
                        $j = 0;
                    }else{//子集处理
                        $cur->sub->$j = $tmpdata;
                        $j++;
                    }
                    
                    $html = preg_replace($preg, '', $html, 1);//匹配成功后去掉匹配成功的部分
                    
                    //判断下一个是同级元素还是子元素
                    preg_match('/<(.|\n)*/',$html, $submatch);//去掉“<”之前的字符
                    $subhtml = $submatch[0];
                    $old_issub = $issub;
                    if(mb_substr($subhtml,0,2) != "</"){//子元素 
                        $issub = true;
                    }else{//同级元素
                        preg_match('/<[^\/](.|\n)*/',$html, $submatch);//将“</>”去掉，获取第一个非“</”的“<...”字符串
                        $html = $submatch[0] ?? '';
                        $issub = false;
                    }
                    
                    
                    $ii++;
                }
                
                // echo "<pre>";
                // var_dump($jsonArr);
                return $data;
            }
            // var_dump($match);
            // var_dump($html);
            // var_dump($jsonArr);
    
        }
    }

    
    $url = [
        '0'=>'http://blog.tmumu.cn/detail?art_id=26',
        '1'=>"http://baijiahao.baidu.com/s?id=1596984347402187177&wfr=spider&for=pc"
    ];
    $html = file_get_contents($url[0]);
    // $file = fopen('html.txt', 'a+');
    // fwrite($file, $html);
    // fclose($file);
    // $html = '<div style="color:red;width:13px;" >
    // </div>
    // <aa>你好</aa>
    // <span style="color:yy;">vvv</span>
    // <aa><bb><cc><dd></dd></cc></bb></aa>
    // <divv></divv>
    // <div><jj></jj></div>
    // ';
    $htmlclass = new TmumuHtml($html);
    // $str = '<div style   ="color:red;width:13px;">';
    // $jsonstyle = $htmlclass->getStyle($str);
    // var_dump( (object)json_decode($jsonstyle));

    // $domstr = '<div style="color:red;width:13px;" >
    // <ddd>
    // <aa>你好</aa>
    // <span style="color:yy;">vvv</span>
    // </ddd>
    // </div>';
    
    $arro = $htmlclass->domobj;
    


?>
<script>
        window.arro = '<?php echo json_encode($arro)?>';

        console.log(JSON.parse(window.arro));
</script>
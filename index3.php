<?php
    /**
     * 实现html类
     * 用于爬虫之类的支持
     */
    class TmumuHtml{
        public $i = 0;
        public $iarr = [];
        public $issub = false;
        public $data;
        public $level = 0;
        public $cur;
        public $endwithsub = false;
        public $domobj;
        function __construct($html = ""){
            $this->data = new stdClass();
            $this->data->a0 = new stdClass();
            $this->data->sub = new stdClass();
            // var_dump($this->data);die;
            $this->cur = $this->data;
            //去掉
            $html = preg_replace('/(<!Doctype html>|<\/br>|<br>|<!doctype html>|<![^>]*>)/','',$html);
            $html = preg_replace('/(<script(.*?)>)(.|\n)*?(<\/script>)/', '', $html);
            $html = preg_replace('/(<style(.*?)>)(.|\n)*?(<\/style>)/', '', $html);
            $html = preg_replace('/<!--(.*?)-->/', '', $html);
            $html = preg_replace('/(<!--|-->)/', '', $html);
            $html = preg_replace('/<meta(.*?)>/', '', $html);
            $html = preg_replace('/<link(.*?)>/', '', $html);
            $html = preg_replace('/<img(.*?)>/', '', $html);
            $html = preg_replace('/<input[^>]*>/', '', $html);
            $html = preg_replace('/[\n]*/', '', $html);
            $html = preg_replace('/(<br\/>|<br>|<br[^>]*>)/', '', $html);
            // var_dump($html);die;
            // $html = preg_match('/<html[^>]*>/',$html,$match);
            $this->domobj = $this->dom($html);
        }
        public function htmltrim($html = ''){
            return rtrim(ltrim($html, '<'), '>');
        }

        public function increase($i = 1){
            $this->i = $this->i + $i;
        }

        public function iarrPush($i){
            $len = count($this->iarr);
            $this->iarr[$len] = $i;
            
            
            $this->i = 0;
            $this->level  =$this->level + 1;
        }

        public function iarrPop(){
            // echo "当前栈为：";
            // var_dump($this->iarr);
            // echo "执行出栈 当前i: $this->i <br>";
            
            if (count($this->iarr) > 0){
                $this->i = $this->iarr[count($this->iarr)-1] + 1;
            }else{
                $this->i = $this->i + 1;
            }
            
            if($this->level >= 0){
                $this->level = $this->level - 1;
            }
            $count = count($this->iarr)-1;
            unset($this->iarr[$count]);
        }

        public function &updateCur(){
            $tmp = &$this->data;
            $str = "data->";
            $iarr = $this->iarr;
            if($this->level != 0 && $this->level <= count($iarr)){
                for($i = 0,$j = 0; $i < $this->level; $i++,$j++){
                    $arr = 'a' . $iarr[$j];
                    
                    $tmp = &$tmp->$arr->sub;
                    $str .= $arr . "->sub2->";
                    
                    // $tmp = &$tmp->sub;
                }
                $ii = 'a' . $this->i;
                if ($this->issub){
                    $tmp = &$tmp->$ii->sub;
                    $this->endwithsub = true;
                    $str .= "- ->$ii  sub1->";
                }else{
                    $this->endwithsub = false;
                    $tmp = &$tmp->$ii;
                    $str .= "-- -> $ii";
                }
            }
            if($this->level == 0){
                $i = 'a' . $this->i;
                $tmp = &$this->data->$i;
                $this->endwithsub = false;
                $str = "data->" . $i;
            }
            // var_dump($this->iarr);
            // echo "当前为： $str <br>";
            return $tmp;
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
            $ii = 0;
            $preg = '/<[^>]*>/';
            preg_match($preg, $html, $match);
            if (count($match) > 0){
                
                while(preg_match('/<[^\/]/', $html, $tmpdata)){
                    // echo "代码：" . $html;
                    
                    // echo "<br>-----------11-----------------<br><pre>";
                    // echo "i: $this->i  ";
                    // echo "level:$this->level <br>";
                    // var_dump($this->issub);
                    $preg = '/<[^>]*>/';//匹配<>的正则表达式
                    preg_match($preg, $html, $match);
                    preg_match('/[^<][^ |^>]*/', $match[0],  $title);//获取html标签名曾
                    $tmpdata = new stdClass();
                    $tmpdata->name = $title[0];
                    $tmpdata->style = new stdClass();
                    $tmpdata->sub = new stdClass();
                    $mytmp = &$this->updateCur();//更新“指针”
                    
                    if (!$this->issub){//同级元素处理
                        $i = 'a' . $this->i;
                        $mytmp = $tmpdata;//写入
                        
                        // $this->increase();
                    }else{//子集处理
                        $this->iarrPush($this->i);
                        $i = 'a' . $this->i;
                        if ($this->endwithsub){
                            $mytmp->$i = $tmpdata;//写入
                        }else{
                            $mytmp->sub->$i = $tmpdata;//写入
                        }
                        
                        // $this->increase();
                    }
        
                    $html = preg_replace($preg, '', $html, 1);//匹配成功后去掉匹配成功的部分
                    
                    //判断下一个是同级元素还是子元素
                    preg_match('/<.*/',$html, $submatch);//去掉“<”之前的字符
                    $subhtml = $submatch[0];
                    // echo "判断子层的代码： $html";
                    if(mb_substr($subhtml,0,2) != "</"){//子元素 
                        echo "进入这里1";
                        $this->issub = true;
                        // preg_match('/<[^\/]*(.|\n)*/',$html, $tmumatch);//将“</>”去掉，获取第一个非“</”的“<...”字符串
                        // $html = $tmumatch[0] ?? '';
                        echo "代码子级： $html";
                    }else{//可能同级元素
                        // echo "代码： $subhtml";
                        $subsubhtml= preg_replace('/<\/[^>]*>/', '', $subhtml, 1);//去掉第一个"</>"
                        // echo "代码1： $subsubhtml";
                        $res2 = preg_match('/<[^>]*>/', $subsubhtml, $subsubmatch);
                        if($res2 > 0) {
                            if(mb_substr($subsubmatch[0],0,2) != "</"){//同级元素 
                                echo "代码同级： $html";
                                echo "进入这里2";
                                preg_match('/<[^\/].*/',$html, $submatch);//将“</>”去掉，获取第一个非“</”的“<...”字符串
                                $html = $submatch[0] ?? '';
                                // echo "代码 $html";
                                $this->issub = false;
                                $this->increase();
                            }else{//计算层级
                                echo "代码层级： $html";
                                echo "进入这里3";
                                $this->issub = false;
                                // echo "要匹配的字符： $subsubhtml";
                                $tmphtml = preg_replace('/<[^\/]*>/', '<div>', $subsubhtml);
                                // echo "匹配之后： $tmphtml";
                                $index = strpos($tmphtml, '<div>');
                                preg_match_all('/<\/[^>]*>/', substr($tmphtml,0, $index), $lastmatch);
                                $level = sizeof($lastmatch[0]);
                                echo "回退的层级是：$level <br>";
                                while($level > 0){
                                    $this->iarrPop();
                                    $level--;
                                }
                                preg_match('/<[^\/].*/',$html, $submatch);//将“</>”去掉，获取第一个非“</”的“<...”字符串
                                $html = $submatch[0] ?? '';
                                
                            }
                        }
                    }
                    // var_dump($this->data);
                    $ii++;
                }
                

                return $this->data;
            }

    
        }
    }
    error_reporting(E_ERROR);
    set_time_limit(300);
    
    $url = [
        '0'=>'http://blog.tmumu.cn/detail?art_id=26',
        '1'=>"http://baijiahao.baidu.com/s?id=1596984347402187177&wfr=spider&for=pc",
        '2'=> 'html.html',
        '3'=>'http://lamda.nju.edu.cn/CH.MainPage.ashx'
    ];
    $html = file_get_contents($url[2]);

    $htmlclass = new TmumuHtml($html);
    
    $arro = $htmlclass->domobj;
    echo "<pre>";
    var_dump($arro);


?>
<script>
        window.arro = '<?php echo json_encode($arro)?>';

        console.log(JSON.parse(window.arro));
</script>
<?php

namespace AppBundle\Controller\Wechart;

class WxServletController{

    public function doGetAction(){
        //定义 token，token在微信公众号后台设置
        define("TOKEN", "jwjy");
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
            $this->responseMsg();
        }
    }

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            libxml_disable_entity_loader(true);//安全防护
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $openId = (string)$postObj->FromUserName;
            $msgType = $postObj->MsgType;

            if($msgType == 'text'){
                if($postObj->Content == 'XX'){
                    $data = array(
                        'touser' => $openId,
                        'msgtype' => 'mpnews',
                        'mpnews' => array(
                            "media_id" => 'MEDIA_ID'
                        )
                    );
                    //发送图文
                    $this->sendCustomMessage($data);
                }elseif($postObj->Content == 'YY'){
                    echo $this->_response_image($postObj,'MEDIA_ID');
                }
            }

            if($msgType == 'event'){

                if($postObj->Event == 'subscribe'){ //关注
                    echo $this->_response_text($postObj,"欢迎关注XXX！");
                }

                if($postObj->EventKey == 'SUGGESTION'){
                    echo $this->_response_text($postObj,"回复信息");
                }
            }
        }
        exit;
    }

    private function _response_text($object,$content){//文本回复
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content></xml>";

        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $resultStr;
    }

    private function _response_news($object,$newsContent){//图文回复
        $newsTplHead = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime><![CDATA[%s]]></CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%d</ArticleCount><Articles>";

        $newsTplBody = "<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url></item>";

        $newsTplFoot = "</Articles></xml>";

        // $newsTplHead
        $resultStr = sprintf($newsTplHead, $object->FromUserName, $object->ToUserName, time(), 1);

        // $newsTplBody

        $resultStr .= sprintf($newsTplBody, $newsContent['title'], $newsContent['desc'], $newsContent['picUrl'], $newsContent['url']);

        // $newsTplFoot
        $resultStr .= $newsTplFoot;

        return $resultStr;
    }

    //回复图片
    public function _response_image($object,$mediaId){
        $imgTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime><![CDATA[%s]]></CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image><MediaId><![CDATA[%s]]></MediaId></Image>
</xml>";
        $resultStr = sprintf($imgTpl, $object->FromUserName, $object->ToUserName, time(), $mediaId);
        return $resultStr;
    }

    private function checkSignature()
    {
        if (!defined("TOKEN")) {
            throw new \Exception('TOKEN is not defined!');
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    //创建菜单
    public function createMenuAction(){
        $menu = array(
            'button' => array(
                array(
                    'name' => 'first',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => 'NAME',
                            'url' => 'URL',
                        ),
                        array(
                            'type' => 'view',
                            'name' => 'NAME',
                            'url' => 'URL',
                        ),
                    ),
                ),

                array(
                    'name' => 'second',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => 'NAME',
                            'url' => 'URL',
                        ),
                        array(
                            'type' => 'click',
                            'name' => 'NAME',
                            'key' => 'SEARCH_TEACHER',
                        ),
                    ),
                ),

                array(
                    'name' => '在线学习',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => 'NAME',
                            'url' => 'URL',
                        ),
                        array(
                            'type' => 'click',
                            'name' => 'NAME',
                            'key' => 'SUGGESTION',
                        ),
                        array(
                            'type' => 'view_limited',
                            'name' => '下载APP',
                            'media_id' => 'MEDIA_ID',  //图文素材
                        ),
                        array(
                            'type' => 'miniprogram',
                            'name' => '小程序名字',
                            'url' => 'URL',
                            'appid' => 'APPID',
                            'pagepath' => 'pages/index/index',//小程序首页
                        ),
                    ),
                ),
            ),
        );
        $result = $this->createMenu($menu);
        return $this->createJsonResponse($result);
    }

    //创建菜单
    protected function createMenu($menu){
        $token = $this->getToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token['token'];
        $result = $this->https_request($url,json_encode($menu,JSON_UNESCAPED_UNICODE));
        return $result;
    }

    //发送客服信息
    protected function sendCustomMessage($data){
        $token = $this->getToken();
        $url ='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token['token'];
        return $this->https_request($url,json_encode($data,JSON_UNESCAPED_UNICODE));
    }

    // 如果data是一维数组，可以不转json，否则需要转json传递
    protected function https_request($url, $data = null) {
        $Curl = curl_init();
        curl_setopt($Curl, CURLOPT_URL, $url);
        curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($Curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($Curl, CURLOPT_POST, 1);
            curl_setopt($Curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($Curl, CURLOPT_RETURNTRANSFER, 1);
        $Output = curl_exec($Curl);
        curl_close($Curl);
        return $Output;
    }

}




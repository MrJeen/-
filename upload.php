<?php

/**
 * 海米分期  上传接口调用
 * Class upload
 */
class upload{

    protected $appId = '';

    protected $secretKey = '';

    //某些接口需要上传文件，使用 \CURLFile
    public function uploadFiles(){
        $file = new \CURLFile($_FILES['file']['tmp_name']);

        $orderNo = '';//订单号

        $url = '';

        $postData = array(
            'appId' => $this->appId,
            'projectNo' => $orderNo,
        );
        $sign = $this->makeSign($postData);
        $postData['signvalue'] = $sign;
        $postData['file'] = $file;
        return $this->_curl($url,$postData);
    }

    protected function makeSign($params) {
        ksort($params);//将参数按key进行排序
        $str = '';
        foreach ($params as $k => $val) {
            $str .= $k.$val; //拼接成 $str = $key1$value1$key2$value2... 的形式
        }
        $str .= $this->secretKey; //结尾再拼上密钥 $partner_key
        $sign = md5($str); //计算md5值
        return $sign;
    }

    // 如果data是一维数组，可以不转json，否则需要转json传递
    protected function _curl($url, $data = null, $header = null) {
        $Curl = curl_init();
        curl_setopt($Curl, CURLOPT_URL, $url);
        curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($Curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        //非必要参数，有些接口会拦截，则需要填写User-Agent
        $headers = array(
            'User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.33 Safari/537.36',
        );

        if($header){
            $headers = array_merge($headers,$header);
        }

        if (!empty($data)) {
            curl_setopt($Curl, CURLOPT_POST, 1);
            curl_setopt($Curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($Curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($Curl, CURLOPT_RETURNTRANSFER, 1);
        $Output = curl_exec($Curl);

        curl_close($Curl);
        $result = json_decode($Output,true);
        return $result;
    }
}

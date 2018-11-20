<?php
/**
 * fsockopen 异步处理，跳过等待
 * Class execute
 */

class execute{

    public function index(){
        //异步处理
        $data = array();
        $url = '/callback/bind';
        $this->asyncExecute($url,'post',$data);
    }

    /**
     * 异步处理
     * @param $path string 完整的url链接
     * @param $method string  get/post
     * @param $param array 传递的参数
     * @throws \Exception
     */
    public function asyncExecute($path,$method,$param){

        $openHost = $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];

        if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')){
            $path = 'https://'.$host.$path;
            $openHost = 'ssl://'.$host;
        }else{
            $path = 'http://'.$host.$path;
        }

        $query = !empty($param) ? http_build_query($param) : '';

        $errno = 0;
        $errstr = '';

        $fp = fsockopen($openHost, $port, $errno, $errstr, 1);

        if($fp){

            stream_set_blocking($fp,0);

            if($method == 'get'){
                $header = "GET $path HTTP/1.1\r\n";
                $header.="Host: $host\r\n";
                $header.="Connection:close\r\n\r\n";
            }else{
                $header = "POST $path HTTP/1.1\r\n";
                $header .= "Host: $host\r\n";
                $header .= "content-length:".strlen($query)."\r\n";
                $header .= "content-type:application/x-www-form-urlencoded\r\n";
                $header .= "connection:close\r\n\r\n";
                $header .= $query;
            }

            fwrite($fp, $header);

            //注释后则不需要等待处理结果

//            file_put_contents("log.php","<?php \n".var_export($header,true),FILE_APPEND);
//            if($method == 'post'){
//                file_put_contents("log.php","<?php \n".var_export(fread($fp, 1024),true),FILE_APPEND);
//            }else{
//                file_put_contents("log.php","<?php \n".var_export(fgets($fp, 128),true),FILE_APPEND);
//            }

            usleep(1000);
            fclose($fp);

        }else{
            throw new \Exception('读取资源失败: '.$errstr.'（#'.$errno.'）');
        }
    }
}


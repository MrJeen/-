<?php

/**
 * 接收请求
 * Class request
 */
class request{

    public function index(){
        //get接收
        $_GET();

        //post接收，
        //Coentent-Type仅在取值为application/x-www-data-urlencoded和multipart/form-data两种情况下，PHP才会将http请求数据包中相应的数据填入全局变量$_POST
        $_POST();

        //不区分请求方式
        $_REQUEST();

        //PHP输入流php://input，读取没有处理过的POST数据，但不能用于enctype=multipart/form-data”
        file_get_contents('php://input', 'r');

    }
}
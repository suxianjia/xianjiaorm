<?php
 include_once __DIR__."/../vendor/autoload.php";
use Suxianjia\xianjiaorm\myConfig;
use Suxianjia\xianjiaorm\myApp;
 
 



 
// Example usage
// cms_article_base
define("myAPP_VERSION" , "1.0.0") ;
define("myAPP_ENV" , "dev")  ;
define("myAPP_DEBUG" , true )  ;
define("myAPP_PATH", __DIR__."/");
define("myAPP_RUNRIMT_PATH", __DIR__."/runtime/");

 
 

 
$App =   myApp::getInstance( );   
$result = $App->getuserinfo(30312 );

echo json_encode($result, JSON_UNESCAPED_UNICODE);




// 使用方法  cd /ocr/code/demo   &&  php82 test.php 

 
 
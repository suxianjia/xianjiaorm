<?php 
namespace Suxianjia\xianjiaorm;
use Suxianjia\xianjiaorm\myConfig;
use Exception;
use Suxianjia\xianjiaorm\orm\myDatabase;
 
use Suxianjia\xianjialogwriter\client\myLogClient;
if (!defined('myAPP_VERSION')) {        exit('myAPP_VERSION is not defined'); }
if (!defined('myAPP_ENV')  ) {          exit ('myAPP_ENV is not defined'); }
if (!defined('myAPP_DEBUG')) {          exit('myAPP_DEBUG is not defined'); }
if (!defined('myAPP_PATH')) {           exit('myAPP_PATH is not defined'); }
if (!defined('myAPP_RUNRIMT_PATH')) {   exit('myAPP_RUNRIMT_PATH is not defined'); }

class myApp {
    private static  $tableName = '';
    private static $contentName = '';
    private static $idName = '';
    private static $app_path = '';
 

    private static $instance = null;
    private static $runtime_path = '';
 

    private function __construct() {
        // Private constructor to prevent direct instantiation
    }
//     public static function getInstance(string $tableName, string $contentName, string $idName): Appocr {
    public static function getInstance(): myApp { 
        if (self::$instance === null) {
            self::init();
            self::$instance = new self();
        }
        return self::$instance;
    }

    private static function init () {
 
    }


    public function getuserinfo ($id ): array {
        $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];

        $results['code'] = 200;
        $results['msg'] = 'Success';
        $results['data']['results'] = myDatabase::getInstance()->insertData( 'image_ocr_log', ['current_id' => $id]  );
        // $results['data']['update'] = myDatabase::getInstance()->updateData('account_user_info',['client_id' => '2w4r4eete'] ,"`user_id` = {$id} ") ;

        // $sql = "SELECT * FROM `account_user_info`  where `user_id` = {$id}";  
        // $queryResult = myDatabase::getInstance()->query($sql);
        // $results['data']['sql']= $sql;
        // $results['data']['results'] = myDatabase::getInstance()->getResults($queryResult  ); // Assuming getResults() is the correct method
        // $results['data']['results'] = myDatabase::getInstance()->selectOne('account_user_info','client_id',"`user_id` = {$id} ");
        return $results;
    }

    public function logwrite (): array{
        $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
        $a = "sdfdfdgr";
        $b = "sdfdfg".time() ;
        $results['data'] = [$a, $b]; 
        myLogClient::getInstance()::writeErrorLog($a,$b );
        return $results;
    }

//    
public static function getTableName( ): string  {
    return self::$tableName;
}
// e
public static  function getContentName( ): string  {
    return self::$contentName;
}
// 
public static function getIdName( ): string  {
    return self::$idName;
}


public static function setAppPath()    {
    self::$app_path = myAPP_PATH;
}

public static function getAppPath(): string {
    self::$app_path = myAPP_PATH;
    return  self::$app_path;
   
}
//  
public static function setRuntimePath(string $path = '') {
    self::$runtime_path = myAPP_RUNRIMT_PATH;
}
public static function getRuntimePath(): string {
    self::$runtime_path = myAPP_RUNRIMT_PATH;
  return  self::$runtime_path;
}
 
  
}

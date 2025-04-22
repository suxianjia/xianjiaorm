<?php
namespace Suxianjia\xianjialogwriter\client;
use Exception;
use Suxianjia\xianjialogwriter\myConfig;
use Suxianjia\xianjialogwriter\orm\myDatabase;
if (!defined('myAPP_VERSION')) {        exit('myAPP_VERSION is not defined'); }
if (!defined('myAPP_ENV')  ) {          exit ('myAPP_ENV is not defined'); }
if (!defined('myAPP_DEBUG')) {          exit('myAPP_DEBUG is not defined'); }
if (!defined('myAPP_PATH')) {           exit('myAPP_PATH is not defined'); }
if (!defined('myAPP_RUNRIMT_PATH')) {   exit('myAPP_RUNRIMT_PATH is not defined'); }
class myLogClient {
    private static $instance = null;
    private static $app_path = ''; // Declare the app_path property
    private static $log_table_name = 'image_ocr_log';
    private static   $create_sql = "CREATE TABLE IF NOT EXISTS `image_ocr_log` (
        `id` int NOT NULL AUTO_INCREMENT COMMENT '唯一标识符',
        `current_id` int DEFAULT NULL COMMENT '当前id',
        `id_name` varchar(40) DEFAULT NULL COMMENT '表ID',
        `content_name` varchar(40) DEFAULT NULL COMMENT '表内容',
        `table_name` varchar(40) DEFAULT NULL COMMENT '表名',
        `image_path` varchar(255) DEFAULT NULL COMMENT '图片路径',
        `image_size` int DEFAULT NULL COMMENT '图片大小',
        `image_path_index` varchar(40) DEFAULT NULL COMMENT '图片路径的MD5值',
        `ocr_data_text` text COMMENT 'OCR数据文本',
        `create_time` timestamp NULL DEFAULT NULL COMMENT '时间戳',
        PRIMARY KEY (`id`),
        KEY `image` (`image_path_index`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ";
    private static $log_file_path =  __DIR__.'/../runtime'; //  
    private static $log_file_name;// =  'log_' . date('Y-m-d--H', time()) . '.txt';  
    private static $runtime_path = __DIR__.'/../runtime';  // 运行时路径
    private static $log_mode = 'mysql' ; //  

  
 
    
    public static function getInstance(   ) {
        if (self::$instance === null) {
            self::init();
            self::$instance = new self();
        }

        return self::$instance;
    }

 private static function init (){
    self::$log_file_name =  'log_' . date('Y-m-d--H', time()) . '.txt';  

    // Load all configuration settings from myConfig
   $config = myConfig::getInstance()::getLogConfig();
 
 $getRuntimePath = myConfig::getInstance()::getRuntimePath();  
       if ( isset( $config['log_file_name'] ) ) {   self::$log_file_name            =   $config['log_file_name']  ;  }
       if ( isset( $config['log_mode'] ) ) {        self::$log_mode                 =   $config['log_mode']  ;       }
       if ( isset(  $getRuntimePath  ) ) {    self::$runtime_path             =    $getRuntimePath ;   }
       if ( isset( $getRuntimePath  ) ) {        self::$log_file_path            =   $getRuntimePath  ;       }
   

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


    public function __destruct() {
        // Close the database connection if it exists
      
    }
    //  self::getTableName() 
    public static function getTableName(): string {
        return self::$log_table_name;
        // return 
    }
    public static function getLogModel(): string {
        return self::$log_mode;
    }
    public static function getLogFilePath(): string {
        return myConfig::getInstance()::getRuntimePath();
        // return self::$log_file_path;
    }
    public static function getLogFileName(): string {
        return self::$log_file_name;
    }
 
    public static function getCreateSql(): string {
        return self::$create_sql; // create_sql create_sql
    }
    // 写入错误日志  myLogClient::getInstance()::writeErrorLog('Error message');
    public static function writeErrorLog(string $errorMessage = '', string $str =""): void {
        $filename = self::getLogFilePath() .'/Error.'.  self::getLogFileName() ;
    // echo "  writeErrorLog filename  :   $filename  \n";
        file_put_contents( $filename, $errorMessage.':'.$str . PHP_EOL, FILE_APPEND);
        unset($errorMessage, $str);
        unset($filename);
    }

    // 写入执行日志  Execution log

    public  static function writeExecutionLog( int $current_id, string $idName, string $contentName, string $tableName,  string $imagePath, string $ocrDataText): array {
        $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
        $filesize = 0;
        if( $imagePath != '' ){ //  获取远程文件大小； 
            $headers = get_headers($imagePath, 1);
            if (isset($headers['Content-Length'])) {
                $filesize = (int) $headers['Content-Length'];
            } else {
                $filesize = 0; // Default to 0 if size cannot be determined
            }
        }
      
 
        $image_ocr_log = [
            'current_id' => $current_id, // 当前id
            'id_name' => $idName, // 表ID
            'content_name' => $contentName, // 表内容
            'table_name' => $tableName, // 表名
            'image_path' => $imagePath, // 图片路径
            'image_size' =>   $filesize , // 图片大小
            'image_path_index' => md5($imagePath), // 图片路径的MD5值
            'ocr_data_text' => $ocrDataText, // OCR数据文本
            'create_time' => date('Y-m-d H:i:s') // 时间戳
        ];

        try {
            if ( self::getLogModel()  == 'file') {
                $filename = self::getLogFilePath() .'/Execution.'.  self::getLogFileName() ;
                file_put_contents($filename, json_encode($image_ocr_log, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
                $results['code'] = 200;
                $results['msg'] = 'Successfully ';
                unset($filename);
            } else if (  self::getLogModel()    == 'mysql') {
                // 保存到mysql数据库
                $mysqli = myDatabase::getInstance()->getConnection();
            
                // 判断表是否存在，不存在则创建 
      
                $stmt = $mysqli->prepare("SHOW TABLES LIKE `" . self::getTableName() . "`");  
                if (!$stmt) {
                    throw new Exception("failed 准备语句失败: " . $mysqli->error);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    $createStmt = $mysqli->prepare(self::$create_sql);
                    if (!$createStmt) {
                        throw new Exception("failed 准备创建表语句失败: " . $mysqli->error);
                    }
                    if (!$createStmt->execute()) {
                        throw new Exception("failed 创建表失败: " . $createStmt->error);
                    }
                    $createStmt->close();
                }
                $stmt->close();

             
                    // Check if a record with the same image_path_index exists
                    $SELECT_SQL = "SELECT `id` FROM `".self::getTableName()."` WHERE `image_path_index` = ?";
                    $stmt = $mysqli->prepare($SELECT_SQL);

                    if (!$stmt) {
                        throw new Exception("failed 准备查询语句失败: " . $mysqli->error);
                    }

                    $stmt->bind_param('s', $image_ocr_log['image_path_index']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        // Record exists, update it
                        $UPDATE_SQL = "UPDATE `". self::getTableName() ."` SET 
                            current_id = ?, 
                            id_name = ?, 
                            content_name = ?, 
                            table_name = ?, 
                            image_path = ?, 
                            image_size = ?, 
                            ocr_data_text = ?, 
                            create_time = ? 
                            WHERE image_path_index = ?";
                        $updateStmt = $mysqli->prepare($UPDATE_SQL);

                        if (!$updateStmt) {
                            throw new Exception("failed 准备更新语句失败: " . $mysqli->error);
                        }

                        $updateStmt->bind_param(
                            'issssisss',
                            $image_ocr_log['current_id'],
                            $image_ocr_log['id_name'],
                            $image_ocr_log['content_name'],
                            $image_ocr_log['table_name'],
                            $image_ocr_log['image_path'],
                            $image_ocr_log['image_size'],
                            $image_ocr_log['ocr_data_text'],
                            $image_ocr_log['create_time'],
                            $image_ocr_log['image_path_index']
                        );

                        if (!$updateStmt->execute()) {
                            throw new Exception("更新数据失败: " . $updateStmt->error);
                        }

                        $updateStmt->close();
                    } else {
                        // Record does not exist, insert it
                        $INSERT_SQL = "INSERT INTO `".self::getTableName()."` (current_id, id_name, content_name, table_name, image_path, image_size, image_path_index, ocr_data_text, create_time) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $insertStmt = $mysqli->prepare($INSERT_SQL);

                        if (!$insertStmt) {
                            throw new Exception("failed 准备插入语句失败: " . $mysqli->error);
                        }

                        $insertStmt->bind_param(
                            'issssisss',
                            $image_ocr_log['current_id'],
                            $image_ocr_log['id_name'],
                            $image_ocr_log['content_name'],
                            $image_ocr_log['table_name'],
                            $image_ocr_log['image_path'],
                            $image_ocr_log['image_size'],
                            $image_ocr_log['image_path_index'],
                            $image_ocr_log['ocr_data_text'],
                            $image_ocr_log['create_time']
                        );

                        if (!$insertStmt->execute()) {
                            throw new Exception("插入数据失败: " . $insertStmt->error);
                        }

                        $insertStmt->close();
                    }

                    $stmt->close();
             
                $results['code'] = 200;
                $results['msg'] = 'Successfully';
            } else {
                $results['code'] = 500;
                $results['msg'] = 'log model error';
            }
        } catch (Exception $e) {
            $results['code'] = 500;
            $results['msg'] = $e->getMessage() . "--" . $e->getLine();
        }

        $results['data']['log_mode'] = self::getLogModel();// $log_mode ;
        return $results;
    }

    public function __clone() {}
    public function __wakeup() {}

}
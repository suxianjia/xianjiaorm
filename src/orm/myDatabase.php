<?php 
namespace Suxianjia\xianjialogwriter\orm;
use Exception;
use mysqli;
use Suxianjia\xianjialogwriter\myConfig;
use Suxianjia\xianjialogwriter\client\myLogClient;
if (!defined('myAPP_VERSION')) {        exit('myAPP_VERSION is not defined'); }
if (!defined('myAPP_ENV')  ) {          exit ('myAPP_ENV is not defined'); }
if (!defined('myAPP_DEBUG')) {          exit('myAPP_DEBUG is not defined'); }
if (!defined('myAPP_PATH')) {           exit('myAPP_PATH is not defined'); }
if (!defined('myAPP_RUNRIMT_PATH')) {   exit('myAPP_RUNRIMT_PATH is not defined'); }

class myDatabase {
    private $database_type = 'mysqli';

    public function __destruct() {
        $this->close();
    }

    private static $instance = null;
    private static $mysqli;
    private static $runtime_path; // Declare the static property
    private static $app_path; // Declare the static property for app path

    private function __construct() {

    }

    public function __clone(): void {}
    public function __wakeup(): void {}

 
    public static function getInstance( ) {

 


        if (self::$instance === null) {
            
            self::init();
            self::$instance = new self();
        }
        return self::$instance;
    }


    private static function init (){
        $config = myConfig::getInstance( )::getDatabaseConfig();
 
            $hostname   = $config['host'] ?? 'localhost';
            $username   = $config['username'] ??  '';
            $password   = $config['password'] ??    '';
            $database   = $config['database'] ??    '';
            $port       = (int) $config['port'] ?? 3306;
        
            self::$mysqli = new mysqli(
                $hostname ,
                $username ,
                $password ,
                $database ,
                $port 
            );
    
            if (self::$mysqli->connect_error) {
                myLogClient::getInstance()::writeErrorLog('Error message', "Connection failed: " .self::$mysqli->connect_error);
                die("Connection failed: " .self::$mysqli->connect_error);
               
            }
      


    }

    public function getConnection(): mysqli {
        return self::$mysqli;
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

    public function close(): void {
        if (self::$mysqli) {
            self::$mysqli->close();
            self::$mysqli = null;
        }
        self::$instance = null; // Destroy the singleton instance
    }
}

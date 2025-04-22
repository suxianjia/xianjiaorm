<?php
namespace Suxianjia\xianjialogwriter\orm;
use PDO;
use PDOException;
use Exception;
use Suxianjia\xianjialogwriter\myConfig;
use Suxianjia\xianjialogwriter\client\myLogClient;
if (!defined('myAPP_VERSION')) {        exit('myAPP_VERSION is not defined'); }
if (!defined('myAPP_ENV')  ) {          exit ('myAPP_ENV is not defined'); }
if (!defined('myAPP_DEBUG')) {          exit('myAPP_DEBUG is not defined'); }
if (!defined('myAPP_PATH')) {           exit('myAPP_PATH is not defined'); }
if (!defined('myAPP_RUNRIMT_PATH')) {   exit('myAPP_RUNRIMT_PATH is not defined'); }
class myPdo
{
    private static string $runtime_path = '';
    private static string $app_path = '';

    private static string $error = '';
    private static string $message = '';


    private static array $config;

    private static myPdo $instance;
    private static string $sql = '';

    private function __construct()    {    }
    private function __clone()    {    }
    public static function getInstance(): myPdo
    {

        if(!isset(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function createclient(  ): \PDO | null
    {
      
        $config = myConfig::getInstance( )::getDatabaseConfig();
 


            $dsn = $config   ["dsn"];//'mysql:host=localhost;dbname=mydatabase';
            $username =   $config["username"];//'username';
            $password = $config ["password"];//'password';

            try {
                $pdo = new \PDO($dsn, $username, $password);
                    // 设置错误模式为异常
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
 
                self::setMessages( "成功连接到数据库");
                return $pdo;
            } catch (PDOException $e) {
 
                 self::setMessages( $e->getMessage());
                 myLogClient::getInstance()::writeErrorLog('Error message', "Connection failed: " .$e->getMessage() );

                 return null;
            } catch (Exception $e) {
                // Handle other exceptions
                self::setMessages( $e->getMessage());
                myLogClient::getInstance()::writeErrorLog('Error message', "Connection failed: " .$e->getMessage() );
                return null;
            }
    }

 


 
    // Existing methods and properties

    public static function getcounts($whereStr, $tablename) {
        $client = self::createclient();
        // Example implementation: Adjust this query as per your database schema
        $sql = "SELECT COUNT(*) as `count` FROM `{$tablename}` WHERE {$whereStr}";
        self::setSql( $sql  );
        $stmt = $client->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
 


    public static  function getdata($offset = 0,$limit = 10,$field = "", $whereStr =  "1 = 1", $tablename = 'account_ad' , $key='id'):array | null
    {
        $client = self::createclient();
        if ($client == null ) { return null;}
        if ($whereStr == '') { $whereStr == '1 = 1';}
        $sql = "SELECT {$field} FROM `{$tablename}` WHERE {$whereStr} ORDER BY `{$key}` DESC LIMIT  :start , :num  ";
        self::setSql( $sql  );
        $stmt = $client->prepare($sql);
        $stmt->bindValue(':start', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':num',$limit,\PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static  function getMessage( ): string
    {
    return self::$message;
    }
    public static  function getError(): string
    {
        return self::$error;
    }
    public static  function  setMessages( string $message ): void
    {
         self::$message = $message;
    }
    public static  function  setError( string $error ): void
    {
        self::$error = $error;
    }

    public static function setConfig( array $config ): void
    {
        self::$config = $config;
    }
    public static function getConfig(): array
    {
        return self::$config;
    }

    public static function setSql(string $sqlstr = ''): void
    {
        self::$sql = $sqlstr;
    }
    public static function getSql(): string
    {
        return self::$sql;
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
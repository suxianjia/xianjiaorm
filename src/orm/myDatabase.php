<?php 
namespace Suxianjia\xianjiaorm\orm;
use Exception;
use mysqli;
use Suxianjia\xianjiaorm\myConfig;
use Suxianjia\xianjialogwriter\client\myLogClient;//suxianjia/xianjialogwriter 
use mysqli_result;
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

 
    // 查询分页  $this->getConnection() mysql page 分页
    public function queryPage(string $sql, int $page, int $pageSize): array {
        $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];

        // Calculate offset for pagination
        $offset = ($page - 1) * $pageSize;
        $paginatedSql = $sql . " LIMIT $offset, $pageSize";

        $result = $this->getConnection()->query($paginatedSql);
        if ($result === false) {
            myLogClient::getInstance()::writeErrorLog('SQL Error: ' . $paginatedSql, "Query failed: " . $this->getConnection()->error);
            return ['code' => 500, 'msg' => "Query failed: " . $this->getConnection()->error, 'data' => []];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free();

        $results['code'] = 200;
        $results['msg'] = 'Success';
        $results['data'] = $data;

        return $results;
    }

    // 查询一条数据  增加了  joinStr
    public function selectOne(string $tableName, string $columnString = '*', string $whereString = '', string $joinStr = ''): ?array {
        $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
        $sql = "SELECT $columnString FROM `$tableName` $joinStr WHERE $whereString LIMIT 1";
        $result = $this->getConnection()->query($sql);

        if ($result === false) {
            myLogClient::getInstance()::writeErrorLog('SQL Error: ' . $sql, "Select failed: " . $this->getConnection()->error);
            return ['code' => 500, 'msg' => "Select failed: " . $this->getConnection()->error . " | SQL: $sql", 'data' => []];
        }

        $row = $result->fetch_assoc();
        $result->free();

        $results['code'] = 200;
        $results['msg'] = 'Success';
        $results['data'] = $row ?: null;
        return $results;
    }

    // 更新数据
    public function updateData(string $tableName, array $data, string $whereString = ''): array {
        $results = ['code' => 500, 'msg' => 'Failed', 'data' => []   ];
        if (empty($data)) {
            myLogClient::getInstance()::writeErrorLog('SQL Error', "Update failed: No data provided for update.");
            return ['code' => 500, 'msg' => 'Insert failed: No data provided for insert.', 'data' => []];
        }

        $updateString = implode(', ', array_map(function ($key, $value) {
            $escapedValue = $this->getConnection()->real_escape_string($value);
            return "`$key` = '$escapedValue'";
        }, array_keys($data), $data));

        $sql = "UPDATE `$tableName` SET $updateString";
        if (!empty($whereString)) {
            $sql .= " WHERE $whereString";
        }

        $result = $this->getConnection()->query($sql);
        if ($result === false) {
            myLogClient::getInstance()::writeErrorLog('SQL Error', "Update failed: " . $this->getConnection()->error . " | SQL: $sql");
            return ['code' => 500, 'msg' => " SQL Error' Update failed: " . $this->getConnection()->error . " | SQL: $sql", 'data' => []];
        }

        return        $results ;
    }
// 删除数据 
public function deleteData(string $tableName, string $whereString = ''): array {
    $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
    if (empty($whereString)) {
        myLogClient::getInstance()::writeErrorLog('SQL Error', "Delete failed: No condition provided for delete.");
        return ['code' => 500, 'msg' => 'Delete failed: No condition provided for delete.', 'data' => []];
    }

    $sql = "DELETE FROM `$tableName` WHERE $whereString";

    $result = $this->getConnection()->query($sql);
    if ($result === false) {
        myLogClient::getInstance()::writeErrorLog('SQL Error', "Delete failed: " . $this->getConnection()->error . " | SQL: $sql");
        return ['code' => 500, 'msg' => "Delete failed: " . $this->getConnection()->error . " | SQL: $sql", 'data' => []];
    }

    $results['code'] = 200;
    $results['msg'] = 'Success';
    $results['data'] = ['affected_rows' => $this->getConnection()->affected_rows];
    return $results;
}


    // 插入数据 
public function insertData(string $tableName, array $data): array {
    $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
    if (empty($data)) {
        myLogClient::getInstance()::writeErrorLog('SQL Error', "Insert failed: No data provided for insert.");
        return ['code' => 500, 'msg' => 'Insert failed: No data provided for insert.', 'data' => []];
    }

    $columns = implode(', ', array_keys($data));
    $values = implode(', ', array_map(function ($value) {
        return "'" . $this->getConnection()->real_escape_string($value) . "'";
    }, $data));

    $sql = "INSERT INTO `$tableName` ($columns) VALUES ($values)";

    $result = $this->getConnection()->query($sql);
    if ($result === false) {
        myLogClient::getInstance()::writeErrorLog('SQL Error', "Insert failed: " . $this->getConnection()->error . " | SQL: $sql");
        return ['code' => 500, 'msg' => "Insert failed: " . $this->getConnection()->error . " | SQL: $sql", 'data' => []];
    }

    $results['code'] = 200;
    $results['msg'] = 'Success';
    $results['data'] = ['insert_id' => $this->getConnection()->insert_id];
    return $results;
}




// 查找数据 $this->getConnection()  mysqli getResults 
public function query(string $sql): array {
    $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
    $result = $this->getConnection()->query($sql);

    if ($result === false) {
        myLogClient::getInstance()::writeErrorLog('SQL Error', "Query failed: " . $this->getConnection()->error);
        return ['code' => 500, 'msg' => "Query failed: " . $this->getConnection()->error, 'data' => []];
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->free();

    $results['code'] = 200;
    $results['msg'] = 'Success';
    $results['data'] = $data;

    return $results;
}
// 执行语句 如 删除 更新 插入  $this->getConnection() mysqli execQuery
public function execQuery(string $sql): array {
    $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
    $result = $this->getConnection()->query($sql);

    if ($result === false) {
        myLogClient::getInstance()::writeErrorLog('SQL Error', "Execution failed: " . $this->getConnection()->error . " | SQL: $sql");
        return ['code' => 500, 'msg' => "Execution failed: " . $this->getConnection()->error . " | SQL: $sql", 'data' => []];
    }

    $results['code'] = 200;
    $results['msg'] = 'Success';
    $results['data'] = ['affected_rows' => $this->getConnection()->affected_rows];
    return $results;
}


// $this->getConnection() mysqli SELECT COUNT  增加了  joinStr
public static function getCounts(string $tableName, string $whereStr = '1', string $joinStr = ''): array {
    $results = ['code' => 500, 'msg' => 'Failed', 'data' => []];
    $sql = "SELECT COUNT(*) as count FROM `$tableName` $joinStr WHERE $whereStr";

    $result = self::getInstance()->getConnection()->query($sql);
    if ($result === false) {
        myLogClient::getInstance()::writeErrorLog('SQL Error', "Count query failed: " . self::getInstance()->getConnection()->error . " | SQL: $sql");
        return ['code' => 500, 'msg' => "Count query failed: " . self::getInstance()->getConnection()->error, 'data' => []];
    }

    $row = $result->fetch_assoc();
    $result->free();

    $results['code'] = 200;
    $results['msg'] = 'Success';
    $results['data'] = ['count' => $row['count'] ?? 0];

    return $results;
}

// end 

}
<?php
/**
 * mysql数据库操作类
 * 设计目标：
 * 1、实例化时即连接上mysql数据库
 * 2、可以单独设定连接编码
 * 3、可以单独设定要使用的数据库
 * 4、可以主动关闭连接
 */

class MySQLDB{
    
    private $link = null; //用于存储数据库连接资源
    private $host;
    private $port;
    private $userName;
    private $passWord;
    private $charset;
    private $dbname;

    function __construct($config)  {

        $this->host     = !empty($config['host'])     ? $config['host'] : 'localhost';
        $this->port     = !empty($config['port'])     ? $config['port'] : 3306;
        $this->userName = !empty($config['userName']) ? $config['userName'] : 'root';
        $this->passWord = !empty($config['passWord']) ? $config['passWord'] : '';
        $this->charset  = !empty($config['charset'])  ? $config['charset'] : utf8;
        $this->dbname   = !empty($config['dbname'])   ? $config['dbname'] : '';
        //连接数据库
        $this->link = mysql_connect("{$this->host}:{$this->port}",$this->userName,$this->passWord) or die('数据库连接失败');
        //设置连接编码
        $this->setCharset($this->charset);
        //设置数据库
        $this->selectDB($this->dbname);
    }
    //可以设定连接编码
    function setCharset($charset){
        mysql_query("set names $charset");
    }
    //可以设定要使用的数据库
    function selectDB($dbname){
        mysql_query("use $dbname");
    }

    function exec($sql){
        $result = mysql_query($sql);
        if($result===false){
            echo "sql语句执行失败,请参考下列信息:";
            echo "<br>错误号:" . mysql_errno();
            echo "<br>错误信息:" . mysql_error();
            echo "<br>错误语句:" . $sql;
            die();
        }else{
            return true;
        }
    }
    
    //增加数据
    /**
        $tableName:表名
        $data:插入记录数组
        字段名=>字段值
    **/


    function add($tableName,$data){
        $fields='';
        $values='';
        foreach($data as $k=>$v){
            $fields.=$k.',';
            $values.=$v.',';
        }
        $sql =  "insert into $tableName ";
        $sql.="(`" . implode("`, `" , array_keys($data)) . "`)";
        $sql.=" values ('" . implode("', '" , $data) . "')";
        //$result = mysql_query($sql) or die(mysql_error());
        $result = mysql_query($sql);
        if($result===false){
            return '<br>'.mysql_error();
        }else{
            return true;
        }
    }

    function getOneRow($sql){
        $result = mysql_query($sql);
        if($result===false){
            echo "sql语句执行失败,请参考下列信息:";
            echo "<br>错误号:" . mysql_errno();
            echo "<br>错误信息:" . mysql_error();
            echo "<br>错误语句:" . $sql;
            die();
        }
        $rec = mysql_fetch_assoc($result);
        return $rec;
    }

    //主动关闭连接
    function closeDB(){
        mysql_close($this->link);
    }

}

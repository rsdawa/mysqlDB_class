<?php
class MySQLDB
{
    private $link = null;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $charset;
    private $dbname;
    //单例化第1步：定义私有静态属性存储单例对象
    private static $instance = null;
    //单例化第2步：将构造方法私有化：
    private function __construct($conf)
    {
        //保存连接参数至属性
        $this->host    = $conf['host'] ? $conf['host'] : "localhost";
        $this->port    = $conf['port'] ? $conf['port'] : "3306";
        $this->user    = $conf['user'] ? $conf['user'] : "root";
        $this->pass    = $conf['pass'] ? $conf['pass'] : "";
        $this->charset = $conf['charset'] ? $conf['charset'] : "utf8";
        $this->dbname  = $conf['dbname'] ? $conf['dbname'] : "";
        $this->connect();
    }
    //单例化第3步：设定一个静态方法，并判断是否需要new一个对象返回
    public static function getDB($conf)
    {
        if (empty(self::$instance)) {
            self::$instance = new self($conf);
        }
        return self::$instance;
    }
    //可以单独修改要使用的数据库
    public function selectDatabase($dbname)
    {
        $this->query("use $dbname");
        $this->dbname = $dbname;
    }
    //单独设置连接编码
    public function selectCharset($charset)
    {
        $this->query("set names $charset");
        $this->charset = $charset;
    }
    //关闭数据库
    public function close()
    {
        mysql_close($this->link);
    }

    //专门执行sql语句，并进行错误处理
    //执行成功后，直接返回数据
    public function query($sql)
    {
        $result = mysql_query($sql, $this->link);
        if ($result === false) {
            header("content-type:text/html;charset=utf-8");
            echo "<p>发生错误，详细信息：</p>";
            echo "<br>错误语句：" . $sql;
            echo "<br>错误信息：" . mysql_error();
            echo "<br>错误代号：" . mysql_errno();
            die(); //失败之后，直接终止
        } else {
            return $result;
        }
    }
    //执行没有返回结果集的增删改语句，成功返回真，失败返回假
    public function exec($sql)
    {
        return $this->query($sql);
    }
    //该方法可以执行select语句，并将数据以二维数组的形式返回
    public function getRows($sql)
    {
        $result = $this->query($sql);
        $arr    = array();
        while ($rec = mysql_fetch_assoc($result)) {
            $arr[] = $rec;
        }
        return $arr;
    }
    //该方法返回一行多列数据
    public function getOneRow($sql)
    {
        $result = $this->query($sql);
        if ($rec = mysql_fetch_assoc($result)) {
            return $rec;
        } else {
            return array();
        }
    }
    //该方法返回一行一列数据（标量数据）
    public function getOneData($sql)
    {
        $result = $this->query($sql);
        if ($rec = mysql_fetch_row($result)) {
            return $rec[0];
        } else {
            return false;
        }
    }
    //定义序列化对象时写入的属性
    public function __sleep()
    {
        return array('host', 'port', 'user', 'pass', 'charset', 'dbname');
    }
    //反序列化时执行
    public function __wakeup()
    {
        $this->connect();
    }
    //连接数据库
    private function connect()
    {
        //连接数据库
        $this->link = mysql_connect("$this->host:$this->port", $this->user, $this->pass) or die('数据库服务器连接失败!');
        //设置连接编码
        $this->selectCharset($this->charset);
        //选定数据库
        $this->selectDatabase($this->dbname);
    }

}

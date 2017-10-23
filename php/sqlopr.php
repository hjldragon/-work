<?php
/*
 * ${xxx}
 * 数据库操作类
 *
 * modify: 2014-03-17 v0.1
 */

// 用于操作sql的数据，应继承些类；
abstract class SqlData
{
    // 把array中对应元素copy到成员中（一一对应）
    public function FromAry($ary)
    {
        $ref = get_object_vars($this);
        if(count($ary) >= count($ref))
        {
            $i = 0;
            foreach($ref as $var => &$value)
            {
                $this->$var = is_int($this->$var) ? (int)$ary[$i] : $ary[$i];
                $i++;
            }
        }
        return $this;
    }
};




// 数据库操作类
class SqlOpr
{
    const T_INT    = 1;      // array中元素类型
    const T_STRING = 2;      // array中元素类型
    private $m_connect = NULL;
    private $m_prepare = NULL;
    private $m_parser  = NULL;
    private $m_sql     = '';
    private $m_params  = NULL;
    private $m_errmsg  = '';

    // 只一个参数时，第一个参数是连接，即new PDO(...)返回的对象；
    // function __construct($connect)
    function __construct($host, $port=NULL, $user=NULL, $passwd=NULL, $dbname=NULL, $charset=NULL, $dbms=NULL)
    {
        if(func_num_args() == 1 && $host instanceof PDO)
        {
            $this->m_connect = $host;
        }
        else
        {
            if(!$charset)
            {
                $charset = 'utf8';
            }
            if(!$dbms)
            {
                $dbms = 'mysql';
            }
            //$this->m_connect = $connect;
            if($dbname)
            {
                $dbname = "dbname=$dbname;";
            }
            $dsn="$dbms:host=$host;port=$port;$dbname";
            // LogDebug("dsn=$dsn, user=$user, passwd=$passwd, charset=$charset");
            try
            {
                $this->m_connect = new PDO($dsn, $user, $passwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES \"${charset}\";"));   // 数据库长连接: PDO::ATTR_PERSISTENT => true
                $this->m_connect->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                $this->m_connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $this->m_connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                /*
                 * 注意和db_pool.php中设置要一致
                 */
            }
            catch(PDOException $e)
            {
                $this->m_errmsg[] = $e->getMessage();
                $this->m_connect = NULL;
            }
            catch(Exception $e)
            {
                $this->m_errmsg[] = $e->getMessage();
                $this->m_connect = NULL;
            }
        }
    }

    // 绑定整数
    public function BindInt($param, $value)
    {
        if(isset($this->m_params["$param"]))
        {
            $this->m_errmsg[] = "int param exist: [$param]";
            return -1;
        }
        $this->m_params["$param"] = array("value"=>(int)$value, "type"=>PDO::PARAM_INT);
        return 0;
    }

    // 绑定字符串
    public function BindString($param, $value)
    {
        if(isset($this->m_params["$param"]))
        {
            $this->m_errmsg[] = "sting param exist: [$param]";
            return -1;
        }
        $this->m_params["$param"] = array("value"=>$value, "type"=>PDO::PARAM_STR);
        return 0;
    }

    // 绑定变量
    private function BindVar()
    {
        if(NULL == $this->m_prepare)
        {
            // Log::err('$this->m_prepare is null.');
            $this->m_errmsg[] = '$this->m_prepare is null.';
            return -1;
        }
        if(!$this->m_params)
        {
            LogDebug('$this->m_params is empty');
            return 0;
        }
        try
        {
            while(list($name, $val)= each($this->m_params))
            {
                $this->m_prepare->bindValue($name, $val["value"], $val["type"]);
            }
        }
        catch(PDOException $e)
        {
            $this->m_errmsg[] = $e->getMessage();
            return -1;
        }
        catch(Exception $e)
        {
            $this->m_errmsg[] = $e->getMessage();
            return -1;
        }
        return 0;
    }

    private function SetSql($sql)
    {
        try
        {
            $this->m_sql = preg_replace('/[\r\n]/', "", $sql);
            if(NULL == $this->m_connect)
            {
                $this->m_errmsg[] = '$this->m_connect is null[1].';
                return -1;
            }
            $this->m_prepare = $this->m_connect->prepare($this->m_sql);
            if(!$this->m_prepare)
            {
                $this->m_errmsg[] = '$this->m_prepare is null[3], sql=[$this->m_sql]';
                return -1;
            }
            return 0;
        }
        catch(PDOException $e)
        {
            //LogErr($e->getMessage());
            $this->m_errmsg[] = $e->getMessage();
            return -1;
        }
        catch(Exception $e)
        {
            //LogErr($e->getMessage());
            $this->m_errmsg[] = $e->getMessage();
            return -1;
        }
        return 0;
    }

    // 取自增长id
    public function GetId()
    {
        return  $this->m_connect->lastInsertId();
    }

    public function GetErrMsg()
    {
        return json_encode($this->m_errmsg);
    }

    public function GetSql()
    {
        // ob_start();
        // $this->m_prepare->debugDumpParams();
        // $sql = ob_get_contents();
        // ob_end_clean();
        $sql = $this->m_sql;
        $params = json_encode($this->m_params);
        return "sql:[$sql] --- params:$params";
    }

    // public function Exec($sql, &$resp=NULL, $classname=NULL)
    // public function Exec($sql, &$resp=SqlOpr::T_INT)
    // public function Exec($sql, &$resp=SqlOpr::T_STRING)
    // public function Exec($sql, &$resp=obj)
    // public function Exec($sql, &$resp=array, $classname="MyObj"|SqlOpr::T_INT|SqlOpr::T_STRING)
    public function Exec($sql, &$resp=NULL, $classname=NULL)
    {
        $ret = $this->SetSql($sql);
        if($ret < 0)
        {
            $this->m_errmsg[] = "\$this->SetSql() err, ret=[$ret]";
            return $ret;
        }
        $ret = $this->BindVar();
        if($ret < 0)
        {
            $this->m_errmsg[] = "\$this->BindVar() err, ret=[$ret]";
            return $ret;
        }

        // don't need test
        //if(NULL == $this->m_connect)
        //if(NULL == $this->m_prepare)

        try
        {
            $ret = $this->m_prepare->execute();
            // $this->m_prepare->debugDumpParams();
            //LogDebug($this->GetSql());
            $code = $this->m_prepare->errorCode();
            if($code != 0)
            {
                $this->m_errmsg[] = $this->m_prepare->errorInfo();
                return -$code;
            }

            if($this->m_prepare->rowcount() > 0 && $this->m_prepare->columncount() > 0)
            {
                // is array
                if(is_array($resp))
                {
                    if(class_exists($classname))
                    {
                        while($row = $this->m_prepare->fetch(PDO::FETCH_NUM))
                        {
                            $obj = new $classname;
                            $resp[] = $obj->FromAry($row); // 行中各列转为对象各值
                        }
                    }
                    elseif(SqlOpr::T_INT === $classname)
                    {
                        while($row = $this->m_prepare->fetch(PDO::FETCH_NUM))
                        {
                            $resp[] = (int)$row[0];         // 只取第一列
                        }
                    }
                    elseif(SqlOpr::T_STRING === $classname)
                    {
                        while($row = $this->m_prepare->fetch(PDO::FETCH_NUM))
                        {
                            $resp[] = $row[0];              // 只取第一列
                        }
                    }
                }
                elseif(isset($resp))
                {
                    $row = $this->m_prepare->fetch(PDO::FETCH_NUM);
                    if($row)
                    {
                        // int or string
                        if(is_int($resp) || is_string($resp))
                        {
                            $resp = is_int($resp) ? (int)$row[0] : $row[0];
                        }
                        // object
                        elseif(is_object($resp))
                        {
                            $resp->FromAry($row);
                        }
                    }
                }
            }
            return $this->m_prepare->rowcount();
        }
        catch(PDOException $e)
        {
            // LogDebug($e->getMessage());
            $this->m_errmsg[] = $e->getMessage();
            return -1;
        }
        catch(Exception $e)
        {
            // LogDebug($e->getMessage());
            $this->m_errmsg[] = $e->getMessage();
            return -1;
        }
        return 0;
    }
};




/*
 * 测试代码
 *
 *
 *  class MyObj extends SqlData
 *  {
 *      public $seq = 0;
 *      public $id = 0;
 *      public $name = '';
 *      public $x = '';
 *
 *      public static function QueryField()
 *      {
 *          return "seq, id, name, x";
 *      }
 *  }
 *
 *  $connect = GetConnect();
 *  $sqlopr = new SqlOpr($connect);
 *
 *  $sql = 'select ' . MyObj::QueryField() . ' from t';// where x=:x';
 *  $list = array();
 *  $sqlopr->Exec($sql, $list, "MyObj");
 *  print_r($list);
 *
 *
 */


?>
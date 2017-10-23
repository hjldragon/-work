<?php

class Log
{
    private function __construct()
    {
        $this->pid = getmypid();
    }
    public function __destruct()
    {
        if($this->handle)
        {
            fclose($this->handle);
        }
    }

    public static function instance()
    {
        if(self::$instance == null)
        {
            self::$instance = new Log;
        }
        return self::$instance;
    }

    public function SetFile($file)
    {
        $this->sFils = $file;
    }

    public function SetLevel($level)
    {
        $this->level = $level;
    }

    public function err($obj)
    {
        if($this->level >= Log::LEVEL_ERR)
        {
            $this->MyLog("err", $obj);
        }
    }
    public function info($obj)
    {
        if($this->level >= Log::LEVEL_INFO)
        {
            $this->MyLog("info", $obj);
        }
    }
    public function debug($obj)
    {
        if($this->level >= Log::LEVEL_DEBUG)
        {
            $this->MyLog("debug", $obj);
        }
    }

    private function MyLog($type, $obj)
    {
        if($this->handle == null && $this->sFils != "")
        {
            $this->handle = fopen($this->sFils, "a");
            if(!$this->handle)
            {
                //echo "err, can't open file: [$this->sFils]\n";
                return -1;
            }
        }
        global $seq;
        $info = debug_backtrace();
        $file = basename($info[2]['file']);
        $line = $info[2]['line'];
        $func = (count($info) > 3 && $info[3]['function']) ? $info[3]['function'] : "main";
        $tt   = $this->TimeField();
        // $pid  = getmypid();
        $seq++;
        // $msg = "<$seq>[$tt][$pid][$file:$line][$func()][$type] ";
        $msg = "<$seq>[$tt][{$this->pid}][$file:$line][$func()][$type] ";

        if(is_string($obj))
        {
            $msg .= "$obj";
        }
        else if(is_object($obj) || is_array($obj))
        {
            //$msg .= var_export($obj, TRUE);
            $msg .= print_r($obj, true);
        }
        else // if(is_string($obj))
        {
            $msg .= "$obj";
        }

        if($this->handle != null)
        {
            fwrite($this->handle, "$msg\n");
        }
        else
        {
            echo "$msg\n";
        }
    }

    private function TimeField()
    {
        list($usec, $sec) = explode(" ", microtime());
        //$tt = strftime("%Y-%m-%d %H:%M:%S", $sec);
        $tt = strftime("%m.%d %H.%M.%S", $sec);
        $tt = sprintf("%s.%u", $tt, $usec*1000000);
        return $tt;
    }

    // 日志文件切换
    private function Shift()
    {
    }

    const LEVEL_ERR = 1;
    const LEVEL_INFO = 2;
    const LEVEL_DEBUG = 3;
    static private $instance = null;
    private $handle = null;
    private $sFils = "";
    private $level = 0;
    private $pid = 0;
}
function LogErr($args1, $args2=null, $args3=null, $args4=null, $args5=null)
{
    Log::instance()->err($args1, $args2, $args3, $args4, $args5);
}
function LogInfo($args1, $args2=null, $args3=null, $args4=null, $args5=null)
{
    Log::instance()->info($args1, $args2, $args3, $args4, $args5);
}
function LogDebug($args1, $args2=null, $args3=null, $args4=null, $args5=null)
{
    Log::instance()->debug($args1, $args2, $args3, $args4, $args5);
}

?>

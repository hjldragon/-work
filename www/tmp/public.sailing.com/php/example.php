<?php
require_once("../../public.sailing.com/php/page_util.php");




/*
 *
 *
 *
 *
 *                          页面输出的示例代码
 *
 *
 *
 *
 */
if(0)
{

$ret = 1;
$resp = (object)[
    "price" => 1.23,
];


// 对应我们原来代码
echo "\n--------------------直接输出---------------------------\n";
\Pub\PageUtil::HtmlOut($ret, $resp);
\Pub\PageUtil::HtmlOut($ret, $resp, ["exit"=>0, "crypt"=>1]);


echo "\n--------------------在回调中处理---------------------------\n";
\Pub\PageUtil::HtmlOut($ret, $resp, [
    "html_out_callback" => function($ret, $data, $opt){
        var_dump($ret);
        var_dump($data);
        var_dump($opt);
    }
]);


// 对应于外包
echo "\n--------------------在全局回调中处理---------------------------\n";
$html_out_callback = function($ret, $data, $opt){
    echo "###ret:\n";
    print_r($ret);
    echo "\n###data:\n";
    print_r($data);
    echo "\n###opt:\n";
    print_r($opt);
};

// function html_out_callback($ret, $data, $opt){
//     echo "###ret:\n";
//     print_r($ret);
//     echo "\n###data:\n";
//     print_r($data);
//     echo "\n###opt:\n";
//     print_r($opt);
// };

\Pub\PageUtil::HtmlOut($ret, $resp);
// \Pub\PageUtil::HtmlOut($ret, $resp, [out=>1]);

}





/*
 *
 *
 *
 *
 *                          mongo操作示例代码
 *
 *
 *
 *
 */
// if(1)
// {
echo "\n======================== mongo操作示例代码 ========================\n";

require_once("../../public.sailing.com/php/mgo_example.php");
use \Pub\Mongodb as Mgo;

$mgo = new Mgo\Example;
$entry = new Mgo\ExampleInfo;

$ret = $mgo->Save($entry);
if(0 != $ret)
{
    // ....出错处理，打日志等...
}
var_dump($ret);



// $mgo = new Mgo\Example;
// $ret = $mgo->Save($entry);
// 合并为下面的
$ret = Mgo\Example::My()->Save($entry);

// }






class ClassName
{
    // 变量风格1
    public $id = null;      // 用户id
    public $name = null;    // 用户名
    public $age = null;     // xxx

    // 变量风格2
    public $id   = null;    // 用户id
    public $name = null;    // 用户名
    public $age  = null;    // xxx

    // 函数风格
    public function f1($x, $y)
    {
        // 匿名函数定义风格
        $func = function($a, $b){
            return 0;
        }

        // 数组风格
        $list = [
            "aaaa",
            "bbbb",
        ];

        // 单个条件的判断
        if(1 == $x)
        {
            return 1;
        }
        else
        {

        }

        // 多个条件的判断（如果多个条件一行上太长，可放为多行，操作符在前）
        if(1 == $x
            && 2 === $y)
        {
            return 1;
        }
        else
        {

        }

        // 循环风格（注：这里的$item前加&）
        foreach($list as &$item)
        {

        }

        // 函数调用时
        f2(1, 2, 3)

        //
        // 函数调用时，如函数参数太多，按以形式
        f3(
            "aaaaaaaaaaaaaaaaaaa",
            "bbb",
            "ccccccccccccccccccccccccccccccccccccc",
            "dddd"
        )
    }
}
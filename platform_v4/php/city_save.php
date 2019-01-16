<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 录入城市等级
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/mgo_city.php");
use \Pub\Mongodb as Mgo;

//录入城市等级
function SaveCityLevel(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $city_name_list = json_decode($_['city_name_list']);
    $city_level     = $_['city_level'];
    $entry          = new Mgo\CityEntry;
    $mgo            = new Mgo\City;

    foreach ($city_name_list as &$v)
    {
        $entry->city_name  = $v;
        $entry->city_level = $city_level;
        $entry->delete     = 0;
        $ret =  $mgo->Save($entry);
        if (0 != $ret) {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['save_city_level']))
{
    $ret = SaveCityLevel($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
//"舟山市","泰安市","孝感市","鄂尔多斯市","开封市","南平市","齐齐哈尔市","德州市","宝鸡市","马鞍山市","郴州市",
//"安阳市","龙岩市","聊城市","渭南市","宿州市","衢州市","梅州市","宜城市","周口市","丽水市","安庆市","三明市",
//"枣庄市","南充市","淮南市","平顶山市","东营市","呼伦贝尔市","乐山市","张家口市","清远市","焦作市","河源市",
//"运城市","锦州市","赤峰市","六安市","盘锦市","宜宾市","榆林市","日照市","晋中市","怀化市","承德市","遂宁市",
//"毕节市","佳木斯市","滨州市","益阳市","汕尾市","邵阳市","玉林市","衡水市","韶关市","吉安市","北海市","茂名市",
//"延边朝鲜族自治州","黄山市","阳江市","抚州市","娄底市","营口市","牡丹江市","大理白族自治州","咸宁市",
//"黔东南苗族侗族自治州","安顺市","黔南布依族苗族自治州","泸州市","玉溪市","通辽市","丹东市","临汾市",
//"眉山市","十堰市","黄石市","濮阳市","豪州市","抚顺市","永州市","丽江市","漯河市","铜仁市","大同市",
//"松原市","通化市","红河哈尼族彝族自治州","内江市"
//
//
//
//
//
//
//"成都市","杭州市","重庆市","武汉市","苏州市","西安市","天津市","南京市","郑州市","长沙市","沈阳市","青岛市","宁波市","东莞市","无锡市",
//"昆明市","大连市","厦门市","合肥市","佛山市","福州市","哈尔滨市","济南市","温州市","长春市","石家庄市","常州市","泉州市","南宁市",
//"贵阳市","南昌市","南通市","金华市","徐州市","太原市","嘉兴市","烟台市","惠州市","保定市","台州市","中山市","绍兴市","乌鲁木齐市",
//"潍坊市","兰州市"
?><?php /******************************以下为html代码******************************/?>


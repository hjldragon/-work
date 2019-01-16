<?php
/*
 * [Rocky 2018-01-07 16:46:39]
 * mongodb数据基类
 */
// namespace DaoMongodb;
namespace Pub\Mongodb;

class BaseInfo
{
    public static function ToList($cursor)
    {
        $list = [];
        foreach($cursor as $item)
        {
            $entry = new static($item); // 子类
            array_push($list, $entry);
        }
        return $list;
    }

    public static function ToObj($cursor)
    {
        if(!$cursor)
        {
            return null;
        }
        $entry = new static; // 子类
        $entry->FromMgo($cursor);
        return $entry;
    }
}

?>

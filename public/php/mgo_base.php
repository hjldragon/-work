<?php
/*
 * [Rocky 2018-01-07 16:46:39]
 * mongodb操作基类
 */
// namespace DaoMongodb;
namespace Pub\Mongodb;

class MgoBase
{
    // 单例缓存
    private static $my = [];

    // 取单例
    public static function My()
    {
        $p = self::$my[static::class];
        if(null == $p)
        {
            $p = new static;
            self::$my[static::class] = $p;
        }
        return $p;
    }

    // 以唯一键字段取数据通用操作
    public function DoGetInfoByKey($id, $field, $delete=0)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            $field => $id,
            'delete'=> ['$in'=>[null,0]],
        ];
        $cursor = $table->findOne($cond);
        if(null === $cursor[$field])
        {
            return null;
        }
        return $cursor;
    }

    // 以唯一键字段删除数据通用操作
    protected function DoBatchDelete($list, $field)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($list as $i => &$item)
        {
            $item = (string)$item;
        }

        $set = array(
            "delete" => 1,
            "lastmodtime" => time()
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            $field => ['$in' => $list]
        ];
        //LogDebug($cond);
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    // 设置或清除位字段(dest_value -- >=0:设置, 0<:清除)
    protected function BitSet($cond_name, $cond_value, $dest_name, $dest_value)
    {
        try
        {
            $db = \DbPool::GetMongoDb();
            $table = $db->selectCollection($this->Tablename());
            $cond = array(
                "$cond_name" => $cond_value,
            );
            $opr = [];
            $dest_value = (int)$dest_value;
            if($dest_value >= 0)
            {
                $opr = ['or' => $dest_value];       // 设置
            }
            else
            {
                $dest_value = -$dest_value;
                $opr = ['and' => ~$dest_value];     // 清除
            }
            $value = [
                '$bit' => [
                    "$dest_name" => $opr,
                ]
            ];
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }
}

?>

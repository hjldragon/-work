<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['sale_off']        = 0;//下架的数据
    $_['pad_food_list']   = true;
    $_['srctype']         = 3;
    require("menu_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    $food_list = $obj->data->list;
    if(!$food_list)
    {
        $obj->data = (object)array(
        );
    }else {
        $shop_pack = $obj->data->shop_pack;
        //LogDebug($food_list);
        $food_all = [];
        foreach ($food_list as $food) {
            $food_info['food_id']   = $food->food_id;
            $food_info['name']      = $food->food_name;
            $food_info['type']      = $food->category_id;
            $food_info['type_name'] = $food->category_name;
            if($food->stock_num_day == 0)
            {
                $food_info['inventory'] = 99999;
            }else{
                $inventory = $food->stock_num_day-$food->day_sell_num;
                if($inventory<0)
                {
                    $food_info['inventory'] = 0;
                }else{
                    $food_info['inventory'] = $inventory;
                }

            }
            $img                    = current($food->food_img_list);//取数组中第一个
            $domain                 = Cfg::instance()->GetMainDomain();
            $food_info['url']       = "http://shop.$domain/php/img_get.php?img=1&imgname=$img";
            if (!$img) {
                $food_info['url'] = null;
            }
            $food_info['time'] = date('Y-m-d H:i:s', $food->lastmodtime);
            $food_info['hot']  = $food->food_num_mon;
            $b                 = [];
            foreach ($food->food_attach_list as $a) {
                $attribute        = (object)[];
                $attribute->taste = $a->title;
                $attribute->value = $a->spc_value;
                array_push($b, $attribute);
            }
            $food_info['attribute'] = $b;
            $price                  = (object)[];
            if ($food->food_price->type == 2)  //使用有规格的价格
            {
                foreach ($food->food_price->price as $p) {
                    //如果是含有规格的
                    //if($p->is_user == 1) {
                    if ($p->spec_type == 3) {
                        $price->min_price = $p->original_price;
                    }
                    if ($p->spec_type == 2) {
                        $price->mid_price = $p->original_price;
                    }
                    if ($p->spec_type == 1) {
                        $price->max_price = $p->original_price;
                    }
//                }else{
//                    $price->min_price = $p->original_price;
//                }
                }
            } elseif ($food->type == 2) {
                $price->price = $food->food_price;
            } else {
                $price->price = $food->food_price->price[0]->original_price;
            }
            $food_info['price'] = $price;
            if (in_array(3, $food->sale_way) && $shop_pack == 1) {
                $food_info['is_pack'] = true;
            } else {
                $food_info['is_pack'] = false;
            }
            array_push($food_all, $food_info);
        }
        $obj->token = $obj->data->token;
        $obj->data  = (object)[
            "ver"  => 2,
            "menu" => $food_all,
        ];
    }
    //LogDebug($obj);
    echo json_encode($obj);
}
Input();

?>
<?php




class Code
{
	public function Init($key)
	{
		$cfg_province_name2code = [
			"北京市" => (object)[
				"code" => 11,
				"name" => "北京市"
			],
			"上海市" => (object)[
				"code"=> 31,
				"name"=> "上海市"
			],
			"天津市" => (object)[
				"code" => 12,
				"name" => "天津市"
			],
			"河北省" => (object)[
				"code"=> 13,
				"name"=> "河北省"
			],
			"山西省" => (object)[
				"code" => 14,
				"name" => "山西省"
			],
			"内蒙古自治区" => (object)[
				"code"=> 15,
				"name"=> "内蒙古自治区"
			],
			"辽宁省" => (object)[
				"code" => 21,
				"name" => "辽宁省"
			],
			"吉林省" => (object)[
				"code"=> 22,
				"name"=> "吉林省"
			],
			"黑龙江省" => (object)[
				"code" => 23,
				"name" => "黑龙江省"
			],
			"江苏省" => (object)[
				"code"=> 32,
				"name"=> "江苏省"
			],
			"浙江省" => (object)[
				"code" => 33,
				"name" => "浙江省"
			],
			"安徽省" => (object)[
				"code"=> 34,
				"name"=> "安徽省"
			],
			"福建省" => (object)[
				"code" => 35,
				"name" => "福建省"
			],
			"江西省" => (object)[
				"code"=> 36,
				"name"=> "江西省"
			],
			"山东省" => (object)[
				"code" => 37,
				"name" => "山东省"
			],
			"河南省" => (object)[
				"code"=> 41,
				"name"=> "河南省"
			],
			"湖北省" => (object)[
				"code" => 42,
				"name" => "湖北省"
			],
			"广东省" => (object)[
				"code"=> 44,
				"name"=> "广东省"
			],
			"广西壮族自治区" => (object)[
				"code" => 45,
				"name" => "广西壮族自治区"
			],
			"海南省" => (object)[
				"code"=> 46,
				"name"=> "海南省"
			],
			"重庆市" => (object)[
				"code" => 50,
				"name" => "重庆市"
			],
			"四川省" => (object)[
				"code"=> 51,
				"name"=> "四川省"
			],
			"贵州省" => (object)[
				"code" => 52,
				"name" => "贵州省"
			],
			"云南省" => (object)[
				"code"=> 53,
				"name"=> "云南省"
			],
			"西藏自治区" => (object)[
				"code" => 54,
				"name" => "西藏自治区"
			],
			"陕西省" => (object)[
				"code"=> 61,
				"name"=> "陕西省"
			],
			"甘肃省" => (object)[
				"code" => 62,
				"name" => "甘肃省"
			],
			"青海省" => (object)[
				"code"=> 63,
				"name"=> "青海省"
			],
			"宁夏回族自治区" => (object)[
				"code" => 64,
				"name" => "宁夏回族自治区"
			],
			"新疆维吾尔自治区" => (object)[
				"code"=> 65,
				"name"=> "新疆维吾尔自治区"
			]
		];

		return $cfg_province_name2info[$key]->code;
	}

}

?>
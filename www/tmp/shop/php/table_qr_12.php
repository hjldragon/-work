<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once("/www/shop.sailing.com/php/page_util.php");
require_once("cache.php");
require_once("mgo_seat.php");

//二维码的html页面，未用到

$_ = $_REQUEST;
$seat_id = $_["seat_id"];
$mgo = new \DaoMongodb\Seat;
$info = $mgo->GetSeatById($seat_id);
$shop_name = \Cache\Shop::GetShopName($info->shop_id);

?>


<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<!-- <title>123</title> -->
		<script>
			! function(e, t) {
				function n() {
					t.body ? t.body.style.fontSize = 12 * o + "px" : t.addEventListener("DOMContentLoaded", n)
				}

				function d() {
					var e = i.clientWidth / 10;
					i.style.fontSize = e + "px"
				}
				var i = t.documentElement,
					o = e.devicePixelRatio || 1;
				if(n(), d(), e.addEventListener("resize", d), e.addEventListener("pageshow", function(e) {
						e.persisted && d()
					}), o >= 2) {
					var a = t.createElement("body"),
						s = t.createElement("div");
					s.style.border = ".5px solid transparent", a.appendChild(s), i.appendChild(a), 1 === s.offsetHeight && i.classList.add("hairlines"), i.removeChild(a)
				}
			}(window, document);
		</script>

	</head>
	<style>
		* {
			padding: 0;
			margin: 0;
		}
		.qr-box {
			width: 12.6rem;
			height: 15.72rem;
			background: url(../img/table_qr.png);
			background-size: 100% 100%;
			position: relative;
			border: 0.013rem solid #FF6F07;
			border-radius: 0.66rem;
		}
		
		.qr-code {
			width: 7.36rem;
			height: 7.28rem;
			/*background: black;*/
			position: absolute;
			top: 3.9rem;
			left: 2.6rem;
		}
		.qr-code img{
			width: 7.36rem;
			height: 7.28rem;
		}
		.seat-name {
			position: absolute;
			top: 12.46rem;
			width: 12.6rem;
			text-align: center;
			font-size: 0.69rem;
			font-family: MicrosoftYaHei-Bold;
			color: rgba(255, 111, 7, 1);
			line-height: 0.73rem
		}
		
		.text-black {
			
			font-size: 0.48rem;
			font-family: MicrosoftYaHei-Bold;
			color: rgba(0, 0, 0, 1);
			line-height: 0.73rem;
		}
		
		.shop-name {
			position: absolute;
			top: 14.46rem;
			width: 12.6rem;
			text-align: center;
			font-size: 0.64rem;
			font-family: PingFang-SC-Heavy;
			color: rgba(0, 0, 0, 1);
			line-height: 0.73rem;
		}
	</style>

	<body>
		<div class="qr-box">
			<!--二维码-->
			<div class="qr-code">
				<img src="<?php echo 'img_get.php?get_seat_qrcode=1&shop_id='.$info->shop_id.'&seat_id='.$info->seat_id?>";/>
			</div>
			<!--餐桌号-->
			<div class="seat-name"><?php echo $info->seat_name; ?><span class="text-black">号桌</span></div>
			<!--店铺名称-->
			<div class="shop-name">
				<?php echo $shop_name; ?>
			</div>
		</div>
	</body>

</html>



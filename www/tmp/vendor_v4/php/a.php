<?php

require_once("current_dir_env.php");
require_once("/home/www/public.sailing.com/php/vendor/msg_util.inc");
use Pub\Vendor;


$ret = Pub\Vendor\MsgUtil::NotifyVendorShipment("", "");
var_dump($ret);

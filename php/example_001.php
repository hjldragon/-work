<?php
// 跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
?>
{
    "ret": 1,
    "data": {
        "list": [
            1,
            2,
            4,
            6
        ],
        "info": {
            "note": "note note note note"
        },
        "yes": "1"
    }
}

<?php
/*
 * [Rocky 2017-06-06 19:15:24]
 * 报表
 *
 */
require_once("current_dir_env.php");

// 页面中变量
$html = (object)array(
    title => '报表',
    user  => (object)array(
             )
);
?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<script src="3rd/artTemplate/template.js"></script>
<script src="3rd/laydate/laydate.js"></script>
<body>
<script type="text/javascript">
$(function() {
    $.get('report.order_byday.html?'+Util.GetTimestamp(), function(resp){
        $("#id_load_orderstatus_byday").html(resp);
    });
    $.get('report.order_bymon.html?'+Util.GetTimestamp(), function(resp){
        $("#id_load_orderstatus_bymon").html(resp);
    });
    $.get('report.order_byyear.html?'+Util.GetTimestamp(), function(resp){
        $("#id_load_orderstatus_byyear").html(resp);
    });
});
</script>
<style type="text/css">
#id_load_orderstatus {
    padding: 1.04248rem;
    margin: 0;
}
</style>
<dir id="id_load_orderstatus_byday">load...</dir>
<dir id="id_load_orderstatus_bymon">load...</dir>
<dir id="id_load_orderstatus_byyear">load...</dir>

</body>
</html>

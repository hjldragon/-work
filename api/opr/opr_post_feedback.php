<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_                       = &$GLOBALS["_"];
    $_['user_feedback_save'] = true;
    $_['srctype']            = 3;
    require_once("user_feedback_save.php");
}

function Output(&$obj)
{
    echo json_encode($obj);
}
Input();
?>
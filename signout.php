<?php

session_start();

$logout = md5($_SESSION["User_ID"]);
$User_ID_md5 = md5($logout);
unset($_SESSION['User_ID']);

session_unset();
session_destroy();

#echo "Logging out ... Please wait ...";
echo "<script>window.location.href='sign_in?logout=$logout&v_1=$User_ID_md5';</script>";
exit();

?>
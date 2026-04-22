<?php
session_start();
session_unset();
session_destroy();
header('Location: /Foodshare3/views/auth/login.php');
exit;
?>

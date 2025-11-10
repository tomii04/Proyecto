<?php
session_start();
session_unset();
session_destroy();
header("Location: ../Backoffice/login.php");
exit;
?>

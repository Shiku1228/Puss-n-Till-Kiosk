<?php
session_start();
session_unset();
session_destroy();
header("Location: ../Log in staff/login_index.php");
exit();
?>

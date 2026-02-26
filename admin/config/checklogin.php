<?php
function check_login()
{
    if (strlen($_SESSION['admin_id']) == 0) {
        $_SESSION["admin_id"] = "";
        // Use relative path instead of building from $_SERVER to prevent open redirect
        header("Location: /client/login.php");
    }
}

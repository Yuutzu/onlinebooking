<?php
session_start();
session_destroy();
header('Location: index.php'); // Redirect to homepage
unset($_SESSION['otp']);
unset($_SESSION['otp_expiry']);
exit();

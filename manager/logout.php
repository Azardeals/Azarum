<?php

require_once '../includes/login-functions.php';
clearLoggedAdminLoginCookie();
session_destroy();
header("Location: index.php");

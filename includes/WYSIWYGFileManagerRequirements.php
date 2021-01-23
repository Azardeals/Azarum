<?php

if (session_id() === "") {
    ob_start();
    session_start();
}

function checkValidUserForFileManager()
{
    $admin = /* 1 ||  */(isset($_SESSION['admin_logged']) && is_numeric($_SESSION['admin_logged']) && intval($_SESSION['admin_logged']) > 0 && strlen(trim($_SESSION['admin_username'])) >= 4);

    if ($admin) {
        global $is_admin_for_file_manager;
        $is_admin_for_file_manager = 1;
    } else {
        /* exit(0); */
    }
}

checkValidUserForFileManager();


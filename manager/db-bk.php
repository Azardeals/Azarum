<?php

require_once './application-top.php';
checkAdminPermission(6);
system('mysql --user=developer --password=developer groupon < /var/www/dv/a/a/groupon.sql');

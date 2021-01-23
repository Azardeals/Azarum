# [YO!DEALS V5](http://www.demo-v4.yo-deals.com)

Project Name: Yo!Deals.
Release Version: TV-5.0.0.20210107
Release Date:07 Jan, 2021
### Features:

**Test Release Version: TV 5.0.2.20201127**

**Test Release Date: 27 Nov, 2020**

#### Feature
- Added Withdraw request feature for seller
- Admin can approve/reject withdraw requests
- Made it compatible with PHP v7.3


**Test Release Version: TV 5.0.1.20201123**

**Test Release Date: 23 Nov, 2020**

#### Minor bug fixes
- Made this product compatible with Windows systems
- Updated DB Files


### Notes:
- Cron URL should be set *http://www.yourdomain.com/cron.php* (Set for every 30 Min)
- Please make sure you have set test environment for Authorize.net testing.
- If any buyer payment is pending, Order will go in pending voucher section, Admin will wait for 30 min (As cron set) and hold the order,
- If any action not performed in given time the order will be updated as cancelled voucher by cron.
- Callback URL for Facebook login: http://www.yourdomain.com/fb-callback.php
- Callback URL for Apple login: http://www.yourdomain.com/apple-callback.php

### Installation:
- Please note your server must match the exact requirements required by the product only then it will work fine.
- You can check the server configurations by running URL : http://www.yourdomain.com/info.php
- Download the files and configure with your development/production environment From "/conf/common-conf.php" and "/conf/yourdomain.com.php"
- Move all folders from 'git-ignored' folder to the root folder on the server and set write permissions to all folders and files.
- If all the server requirements are matching. Now its time to Create a Database and assign a User with all Privileges.
- Upload the database from /git-ignored/database/blank_database.sql.
- Define DB configuration under "/conf/yourdomain.com.php"
- Set the following Cron to execute every one hour and set command "0 */1 * * * wget -q -O /dev/null http://www.yourdomain.com/cron.php"
- Set the following Cron to execute every half hour and set command "0 */1 * * * wget -q -O /dev/null http://www.yourdomain.com/cron_update_order_status.php"
- Update content/settings on the website as per requirements

### Facebook Integration:
- Signup :- Create new app for facebook and enable fb login module, then add redirect url as -
- Facebook Signup redirect Url :- https://www.yourdomain.com/fb-callback.php
- Facebook merchant redirect url :- https://www.yourdomain.com/merchant/merchant-facebook-update.php
- Apple Signup redirect Url :- https://www.yourdomain.com/apple-callback.php
- For merchant :- Facebook Fan page :-
- Add merchant Facebook Fanpage Id. And get FACEBOOK GET ACCESS TOKEN.

### PHP Version:
- PHP 7.4.12 (cli) (built: Oct 31 2020 17:04:09) (NTS)
- Copyright (c) The PHP Group
- Zend Engine v3.4.0, Copyright (c) Zend Technologies
- with Zend OPcache v7.4.12, Copyright (c), by Zend Technologies
- with Xdebug v2.9.8, Copyright (c) 2002-2020, by Derick Rethans

### MySql Version
|Variable_name|Value|
| ------ | ------ |
|innodb_version|5.7.32
|protocol_version|10|
|tls_version|TLSv1,TLSv1.1,TLSv1.2
|version|5.7.32-0ubuntu0.18.04.1|
|version_compile_machine|x86_64|
|version_compile_os|Linux|

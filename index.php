<?php

require_once './application-top.php';
if (!defined("CONF_MESSAGE_ERROR_HEADING")) {
    define("CONF_MESSAGE_ERROR_HEADING", t_lang('M_TXT_THE_FOLLOWING_ERROR_OCCURED'));
}
if (!isset($_SESSION['city']) || !is_numeric($_SESSION['city'])) {
    if (isUserLogged()) {
        selectCity(intval($_SESSION['logged_user']['user_city']));
    }
}
if (!isset($_SESSION['city']) || !is_numeric($_SESSION['city'])) {
    $dealList = $db->query("select count(*) as total,deal_city from tbl_deals as d inner join tbl_cities as c  where d.deal_city=c.city_id and c.city_active=1 and c.city_deleted=0 and c.city_request=0 and d.deal_status=1 and d.deal_deleted=0 and d.deal_complete=1 group by deal_city order by total desc limit 0,1");
    $dealrow = $db->fetch($dealList);
    if (intval($dealrow['deal_city']) > 0) {
        selectCity(intval($dealrow['deal_city']));
    } else {
        $city_to_show = '';
        if ($_SESSION['lang_fld_prefix'] == '_lang1') {
            $city_to_show = ',city_name_lang1';
        }
        $rs = $db->query("select city_id, city_name" . $city_to_show . " from tbl_cities where city_active=1 and city_deleted=0 and city_request=0");
        $row = $db->fetch($rs);
        $_SESSION['cityname'] = $row['city_name'];
        $_SESSION['city'] = intval($row['city_id']);
        $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
    }
}
if (isset($_SESSION['city']) && is_numeric($_SESSION['city'])) {
    require_once __DIR__ . '/home.php';
} else {
    require_once __DIR__ . '/maintenance.php';
}

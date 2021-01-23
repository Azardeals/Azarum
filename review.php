<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
global $db;
$query = "Select company_id,company_id from `tbl_companies` WHERE company_active =1 ";
$rs = $db->query($query);
$expired_deal_ids = $db->fetch_all($rs);
foreach ($expired_deal_ids as $deal) {
    set_review($deal['company_id'], $deal['company_id']);
}

function set_review($deal_id, $deal_company)
{
    echo $deal_id;
    echo "<br/>";
    global $db;
    $data = array('Awesome Quality at Amazing price', 'Love it', 'Best of all', 'Fabulous', 'Did not expected it to be so good', 'Value for money', 'Satisfactory', 'Quality delivered', 'Five Stars');
    foreach ($data as $key => $value) {
        $userArray = array(168, 27, 24, 43, 162, 22, 23, 19, 201, 17, 1, 11, 12, 13);
// Random shuffle
        shuffle($userArray);
// First element is random now
        $randomValue = $userArray[0];
        $stock = rand(4, 5);
        $query = "INSERT INTO
                          `tbl_reviews`(
                            `reviews_type`,
                            `reviews_reviews`,
                            `reviews_rating`,
                            `reviews_reviews_lang1`,
                            `reviews_user_id`,
                            `reviews_company_id`,
                            `reviews_approval`,
                            `reviews_added_on`
                          )
                        VALUES(
                          2,
                          '" . $value . "',
                          '" . $stock . "',
                           '" . $value . "',
                          '" . $randomValue . "',
                         '" . $deal_id . "',
                          1,
                          '" . date('Y-m-d h:i:s') . "'
                        )";
        echo $query;
        $db->query($query);
    }
    echo "review set for <br/>" . $deal_id;
}

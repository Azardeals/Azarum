<?php
require_once './application-top.php';
$post = $_REQUEST;
isset($post['mode']) ? $post['mode']($post) : '';
$post = getPostedData();

function discussionPosts($post)
{
    global $db;
    $countm = $post['countm'];
    $deal_id = $post['deal_id'];
    $rsD = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_deal_discussions dd LEFT OUTER JOIN tbl_users urs on dd.comment_user_id=urs.user_id WHERE comment_approved = '1' and comment_deal_id=$deal_id order by comment_id desc limit 0,2000");
    $countCheck = $db->total_records($rsD);
    $countRow = 0;
    while ($row = $db->fetch($rsD)) {
        $countRow++;
        if (($countRow % 2) == 0) {
            echo ' <li >';
        } else {
            echo ' <li class="still">';
        }
        if ($row['user_avatar'] != "") {
            echo '<img src="' . USER_IMAGES_URL . 'thumb_big_' . $row['user_avatar'] . '" border="0">';
        } else {
            echo '<img src="' . USER_IMAGES_URL . 'user_pic.jpg" alt="" />';
        }
        echo '<a href="javascript:void(0);"><span>' . html_entity_decode($row['user_name']) . ' , ' . displayDate($row['comment_posted_on'], true) . '</span>';
        echo '<br/>' . html_entity_decode($row['comment_title']) . '</a></li>';
        ##### admin answer
        if ($row['comment_comments'] != "") {
            $countRow++;
            if (($countRow % 2) == 0) {
                echo ' <li >';
            } else {
                echo ' <li class="still">';
            }
            echo '<img src="' . CONF_WEBROOT_URL . 'images/user_pic.jpg" alt="" />';
            echo '<a href="javascript:void(0);"><span>' . html_entity_decode($row['user_name']) . ' , ' . displayDate($row['comment_posted_on'], true) . '</span>';
            echo '<br/>' . html_entity_decode($row['comment_comments']) . '</a></li>';
        }
        ##### admin answer	
    }
}

function hidePosts($post)
{
    global $db;
    $countm = $post['countm'];
    $deal_id = $post['deal_id'];
    $rsD = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_deal_discussions dd LEFT OUTER JOIN tbl_users urs on dd.comment_user_id=urs.user_id WHERE comment_approved = '1' and comment_deal_id=$deal_id order by comment_id desc limit 0,1");
    $countCheck = $db->total_records($rsD);
    $countRow = 0;
    while ($row = $db->fetch($rsD)) {
        $countRow++;
        if (($countRow % 2) == 0) {
            echo ' <li >';
        } else {
            echo ' <li class="still">';
        }
        if ($row['user_avatar'] != "") {
            echo '<img src="' . USER_IMAGES_URL . 'thumb_big_' . $row['user_avatar'] . '" border="0">';
        } else {
            echo '<img src="' . CONF_WEBROOT_URL . 'images/user_pic.jpg" alt="" />';
        }
        echo '<a href="javascript:void(0);"><span>' . html_entity_decode($row['user_name']) . ' , ' . displayDate($row['comment_posted_on'], true) . '</span>';
        echo '<br/>' . html_entity_decode($row['comment_title']) . '</a></li>';
        ##### admin answer
        if ($row['comment_comments'] != "") {
            $countRow++;
            if (($countRow % 2) == 0) {
                echo ' <li >';
            } else {
                echo ' <li class="still">';
            }
            echo '<img src="' . CONF_WEBROOT_URL . 'images/user_pic.jpg" alt="" />';
            echo '<a href="javascript:void(0);"><span>' . html_entity_decode($row['user_name']) . ' , ' . displayDate($row['comment_posted_on'], true) . '</span>';
            echo '<br/>' . html_entity_decode($row['comment_comments']) . '</a></li>';
        }
        ##### admin answer	
    }
}

function showReview($post)
{
    global $db;
    $countm = $post['countm'];
    $deal_id = $post['deal_id'];
    $rsD = $db->query("SELECT  * FROM tbl_deal_review dd WHERE  review_deal_id = $deal_id order by review_id desc limit 0, 2000");
    $countCheck = $db->total_records($rsD);
    while ($row = $db->fetch($rsD)) {
        echo '<li><a href="javascript:void(0);">';
        echo html_entity_decode(stripslashes($row['review_reviews']));
        echo '</a></li>';
    }
}

function hideReview($post)
{
    global $db;
    $countm = $post['countm'];
    $deal_id = $post['deal_id'];
    $rsD = $db->query("SELECT  * FROM tbl_deal_review dd WHERE  review_deal_id = $deal_id order by review_id desc limit 0, 2");
    $countCheck = $db->total_records($rsD);
    while ($row = $db->fetch($rsD)) {
        echo '<li><a href="javascript:void(0);">';
        echo html_entity_decode(stripslashes($row['review_reviews']));
        echo '</a></li>';
    }
}

function moreAddress($post)
{
    global $db;
    $countma = $post['countma'];
    $deal_id = intval($post['deal_id']);
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_id', '=', intval($post['deal_id']));
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
    $srch->joinTable('tbl_countries', 'INNER JOIN', 'country.country_id=c.company_country', 'country');
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'c.company_id=ca.company_id', 'ca');
    $srch->addMultipleFields(array('c.company_city' . $_SESSION['lang_fld_prefix'], 'c.company_state', 'ca.*', 'country.country_name' . $_SESSION['lang_fld_prefix']));
    $rsAdd = $srch->getResultSet();
    //echo '<ul id="Address">';
    while ($rowAddress = $db->fetch($rsAdd)) {
        //echo ' <p>';
        ?>
        <div class="locationwrap">
            <img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/location_icon.png">
            <div class="address"> 
                <!--<div class="location_wrapRight">-->
                <p><?php
                    echo $rowAddress['company_address_line1' . $_SESSION['lang_fld_prefix']];
                    $location = $rowAddress['company_address_line1' . $_SESSION['lang_fld_prefix']];
                    if ($rowAddress['company_address_line2' . $_SESSION['lang_fld_prefix']] != '') {
                        echo ' ' . $rowAddress['company_address_line2' . $_SESSION['lang_fld_prefix']];
                        $location .= ', ' . $rowAddress['company_address_line2' . $_SESSION['lang_fld_prefix']];
                    }
                    if ($rowAddress['company_address_line3' . $_SESSION['lang_fld_prefix']] != '') {
                        echo '</br>' . $rowAddress['company_address_line3' . $_SESSION['lang_fld_prefix']];
                        $location .= ', ' . $rowAddress['company_address_line3' . $_SESSION['lang_fld_prefix']];
                    }
                    $location .= ', ' . $rowAddress['company_city' . $_SESSION['lang_fld_prefix']];
                    $location .= ', ' . $rowAddress['company_state'];
                    $location .= ', ' . $rowAddress['country_name' . $_SESSION['lang_fld_prefix']];
                    echo '</br>' . $rowAddress['company_city' . $_SESSION['lang_fld_prefix']] . ' <br/> ' . $rowAddress['company_state'] . ' , ' . $rowAddress['company_address_zip'];
                    ?> 
                    <a href="http://maps.google.com/maps?q=<?php echo $location; ?>" target="_blank"><?php echo t_lang('M_TXT_GET_DIRECTIONS'); ?></a>
                </p>
            </div>
        </div>	 
        <?php
        //echo ' </li><li style="padding-top:5px;">&nbsp;</li>';
    }
    //echo '</ul>';
    ?>
    <div id="vmoreAddress1"  >
        <a class="redLink" style="font-size:15px; margin-left:70px" href="javascript:void(0);" onclick="hideAddress('<?php echo $post['deal_id']; ?>');"><?php echo t_lang('M_TXT_HIDE_LOCATIONS'); ?>
        </a>
    </div>
    <?php
}

function showDescription($post)
{
    global $db;
    $deal_id = intval($post['deal_id']);
    $rsAdd = $db->query("SELECT deal_id,deal_desc" . $_SESSION['lang_fld_prefix'] . " from tbl_deals WHERE `deal_id` = $deal_id   ");
    ?>
    <?php
    $rowDeal = $db->fetch($rsAdd);
    ?>
    <p><?php echo $rowDeal['deal_desc' . $_SESSION['lang_fld_prefix']]; ?></p>
    <?php if (strlen($rowDeal['deal_desc' . $_SESSION['lang_fld_prefix']]) > 350) { ?>
        <a href="javascript:void(0);" class="redLink" style="font-size:15px;" onClick="return hideDescription(<?PHP echo $rowDeal['deal_id']; ?>);" id="vmoreDescription"><?php echo t_lang('M_TXT_HIDE'); ?></a> 
    <?php } ?>
    <?php
}

function hideDescription($post)
{
    global $db;
    $deal_id = intval($post['deal_id']);
    $rsAdd = $db->query("SELECT deal_id,deal_desc" . $_SESSION['lang_fld_prefix'] . " from tbl_deals WHERE `deal_id` = $deal_id   ");
    ?>
    <?php
    $rowDeal = $db->fetch($rsAdd);
    ?>
    <p><?php echo substr($rowDeal['deal_desc' . $_SESSION['lang_fld_prefix']], 0, 350); ?></p>
    <?php if (strlen($rowDeal['deal_desc' . $_SESSION['lang_fld_prefix']]) > 350) { ?>
        <a href="javascript:void(0);" class="redLink" style="font-size:15px;" onClick="return showDescription(<?PHP echo $rowDeal['deal_id']; ?>);" id="vmoreDescription"><?php echo t_lang('M_TEXT_READ_MORE'); ?></a> 
    <?php } ?>
    <?php
}

function hideAddress($post)
{
    global $db;
    $countma = $post['countma'];
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_id', '=', intval($post['deal_id']));
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
    $srch->joinTable('tbl_countries', 'INNER JOIN', 'country.country_id=c.company_country', 'country');
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'c.company_id=ca.company_id', 'ca');
    $srch->setPageNumber(1);
    $srch->setPageSize(1);
    $srch->addMultipleFields(array('c.company_city' . $_SESSION['lang_fld_prefix'], 'c.company_state', 'ca.*', 'country.country_name' . $_SESSION['lang_fld_prefix']));
    $rsAdd = $srch->getResultSet();
    $viewMore = $srch->recordCount();
    while ($rowAddress = $db->fetch($rsAdd)) {
        ?>
        <div class="locationwrap">
            <img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/location_icon.png">
            <div class="address">
                <!--<div class="location_wrapRight">-->
                <p><?php
                    echo $rowAddress['company_address_line1' . $_SESSION['lang_fld_prefix']];
                    $location = $rowAddress['company_address_line1' . $_SESSION['lang_fld_prefix']];
                    if ($rowAddress['company_address_line2' . $_SESSION['lang_fld_prefix']] != '') {
                        echo ' ' . $rowAddress['company_address_line2' . $_SESSION['lang_fld_prefix']];
                        $location .= ', ' . $rowAddress['company_address_line2' . $_SESSION['lang_fld_prefix']];
                    }
                    if ($rowAddress['company_address_line3' . $_SESSION['lang_fld_prefix']] != '') {
                        echo '<br/>' . $rowAddress['company_address_line3' . $_SESSION['lang_fld_prefix']];
                        $location .= ', ' . $rowAddress['company_address_line3' . $_SESSION['lang_fld_prefix']];
                    }
                    $location .= ', ' . $rowAddress['company_city'];
                    $location .= ', ' . $rowAddress['company_state'];
                    $location .= ', ' . $rowAddress['country_name' . $_SESSION['lang_fld_prefix']];
                    echo '<br/>' . $rowAddress['company_city' . $_SESSION['lang_fld_prefix']] . ' , ' . $rowAddress['company_state'] . ' - ' . $rowAddress['company_address_zip'];
                    ?> 
                    <a href="http://maps.google.com/maps?q=<?php echo $location; ?>" target="_blank"><?php echo t_lang('M_TXT_GET_DIRECTIONS'); ?></a>
                </p>
                <!-- </div>-->
            </div>
        </div>
    <?php } if ($viewMore >= 2) { ?>
        <div id="vmoreAddress"><a class="redLink" style="font-size:15px; margin-left:70px" href="javascript:void(0);" onclick="viewMoreAddress('<?php echo $post['deal_id']; ?>');"><?php echo t_lang('M_TXT_view'); ?> <?php echo ($viewMore - 1); ?> <?php echo t_lang('M_TXT_MORE_LOCATIONS'); ?></a></div>
    <?php } ?>  
<?php } ?>			 
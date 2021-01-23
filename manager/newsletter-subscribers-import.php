<?php
require_once './application-top.php';
checkAdminPermission(8);
###### DOWNLOAD XLS FOR THE NEWSLETTER SUBSCRIBERS ##########
$arr_listing = [
    'subs_id' => 'Subscribers ID',
    'subs_email' => 'Email Address',
    'subs_city' => 'City',
    'subs_addedon' => 'Date of subscription',
    'subs_email_verified' => 'Is Verified',
];
$arr_listing1 = ['subs_email' => 'Email Address'];
/** get cities from db * */
$srch_cities = new SearchBase('tbl_cities', 'c');
$srch_cities->addCondition('city_deleted', '=', '0');
$srch_cities->addCondition('city_active', '=', '1');
$srch_cities->addOrder('city_name', 'asc');
$city_listing = $srch_cities->getResultSet();
$countCity = 0;
$cities_arr = [];
while ($city_row = $db->fetch($city_listing)) {
    $cities_arr[$city_row['city_id']] = $city_row['city_name'];
    $countCity++;
    if ($countCity == 1) {
        $city = $city_row['city_id'];
    }
}
$frm_csv = new Form('frmSubscribersCSV');
$frm_csv->setAction($_SERVER['REQUEST_URI']);
$frm_csv->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
$frm_csv->setLeftColumnProperties('width="40%"');
$frm_csv->captionInSameCell(false);
$frm_csv->setFieldsPerRow(1);
$frm_csv->addFileUpload(t_lang('M_TXT_SUBSCRIBERS_CSV_FILE'), 'subscribers_csv');
$frm_csv->addSelectBox(t_lang('M_TXT_SELECT_YOUR_CITY'), 'city', $cities_arr, $_REQUEST['city'], ' ', '', 'city_selector');
$frm_csv->addSubmitButton('&nbsp;', 'submit', t_lang('M_TXT_SUBMIT'), '', 'class="inputbuttons" title="Submit"');
###################IMPORT FEATURE START HERE ##############################
if (is_uploaded_file($_FILES['subscribers_csv']['tmp_name'])) {
    $accepted_files = array('.csv');
    $ext = strtolower(strrchr($_FILES['subscribers_csv']['name'], '.'));
    if (in_array($ext, $accepted_files)) {
        $fp = fopen($_FILES['subscribers_csv']['tmp_name'], 'r');
        $arr = fgetcsv($fp);
        if (count($arr) != 1) {
            $msg->addError('Number of columns in csv must be 1. Your file has ' . count($arr));
        } else {
            $arr_question = [];
            $countUser = 0;
            while ($arr = fgetcsv($fp)) {
                $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email='" . trim($arr[0]) . "' and  subs_city='" . trim($_REQUEST['city']) . "'");
                $result = $db->fetch($check_unique);
                if ($db->total_records($check_unique) == 0) {
                    $countUser++;
                    $record = new TableRecord('tbl_newsletter_subscription');
                    $record->setFldValue('subs_email', trim($arr[0]));
                    $record->setFldValue('subs_city', $_REQUEST['city']);
                    $code = mt_rand(0, 999999999999999);
                    $record->setFldValue('subs_addedon', date('Y-m-d H:i:s'), true);
                    $record->setFldValue('subs_code', $code, '');
                    $record->setFldValue('subs_email_verified', '1', '');
                    $record->addNew();
                }
            }
            $msg->addMsg('File Imported with ' . $countUser . ' subscribers.');
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $msg->addError('Please choose .csv file only.');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}
if ($_REQUEST['mode'] == 'downloadcsv') {
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        global $db;
        $fname = time() . '_sample.csv';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"" . $fname . "\";");
        header("Content-Transfer-Encoding: binary");
        $fp = fopen(TEMP_XLS_PATH . $fname, 'w+');
        if (!$fp) {
            die('Could not create file in temp-images directory. Please check permissions');
        }
        fputcsv($fp, $arr_listing1);
        for ($i = 1; $i < 4; $i++) {
            $arr = [];
            foreach ($arr_listing1 as $key => $val) {
                switch ($key) {
                    case 'subs_email':
                        $arr[] = 'sample' . $i . '@dummyid.com';
                        break;
                }
            }
            if (count($arr) > 0) {
                fputcsv($fp, $arr);
            }
        }
        fclose($fp);
        header("Content-Length: " . filesize(TEMP_XLS_PATH . $fname));
        readfile(TEMP_XLS_PATH . $fname);
        exit;
    } else {
        die('Unauthorized Access.');
    }
}
require_once './header.php';
$arr_bread = array('index.php' => '<img class="home" alt="Home" src="images/home-icon.png">', 'javascript:void(0)' => t_lang('M_TXT_USERS'), '' => t_lang('M_TXT_SUBSCRIBERS'));
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_IMPORT_SUBSCRIBERS'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <?php
    if ($_REQUEST['city'] > 0 || $city > 0) {
        if (checkAdminAddEditDeletePermission(8, '', 'add')) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_IMPORT_SUBSCRIBERS'); ?> </div><div class="content"><?php echo $frm_csv->getFormHtml(); ?></div></div>
                <?php
            }
        }
        ?>	
</td>
<?php require_once './footer.php'; ?>

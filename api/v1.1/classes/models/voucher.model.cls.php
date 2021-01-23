<?php

require_once './includes/page-functions/voucher-functions.php';

class VoucherClass extends ModelClass
{

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function getList($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Voucher List!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $type = array_key_exists('type', $request_data) ? $request_data['type'] : 0;
        $purchase = array_key_exists('purchase', $request_data) ? $request_data['purchase'] : 'desc';
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : 1;
        $srch = fetchVoucherObj($type, $purchase, $page, 20);
        $rs_listing = $srch->getResultSet();
        if ($rows = $db->fetch_all($rs_listing)) {
            foreach ($rows as $key => $row) {

                $rows[$key]['od_deal_price'] = CONF_CURRENCY . number_format($row['od_deal_price'], 2) . CONF_CURRENCY_RIGHT;
                $rows[$key]['cm_shipping_date'] = strtotime($row['cm_shipping_date']);
                $rows[$key]['deal_image'] = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=categorylist';
                //$rows[$key]['deal_tipped_at_time']= displayDate($row['deal_tipped_at'],true, true, $_SESSION['logged_user']['user_timezone']);
                $rows[$key]['deal_tipped_at_time_stamp'] = strtotime(displayDate($row['deal_tipped_at'], true, true, $_SESSION['logged_user']['user_timezone']));
                $rows[$key]['buy_date_time_stamp'] = strtotime(displayDate($row['order_date'], true, true, $_SESSION['logged_user']['user_timezone']));
                $rows[$key]['page-link'] = CONF_WEBROOT_URL . 'print-voucher.php?id=' . $row['order_id'] . $row['cm_counpon_no'];
                $rows[$key]['product-option'] = (get_order_option(array('od_id' => $row['od_id']))) ? get_order_option(array('od_id' => $row['od_id'])) : [];
                if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 1 && $row['dpe_product_file_name'] != "") {
                    $rows[$key]['download-link'] = CONF_WEBROOT_URL . 'download-digital-product.php?product_id=' . $row['deal_id'] . '&id=' . $row['order_id'] . $row['cm_counpon_no'] . '&user_id=' . $_SESSION['logged_user']['user_id'] . '&device=1';
                }
            }
            return $this->prepareSuccessResponse($rows);
        }
        return $this->prepareSuccessResponse('Vouchers list not found!');
    }

    public function getPurchaseHistory()
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Voucher List!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : 1;
        $pagesize = 20;
        $srch = fetchRecordObj($page, $pagesize);
        $rs_listing = $srch->getResultSet();
        $pages = $srch->pages();

        $srch_bal = purchasedhistoryBalanceObj();
        if ($page > 1) {
            $new_start_limit = $pagesize * ($page - 1);
            $pagesize = $pagesize * ($pages);
            $new_limit = "limit $new_start_limit,$pagesize ";
        } else {
            $new_limit = "";
        }

        $rs_left_bal = $db->query("SELECT sum(x.wh_amount) as left_balance_onwards from(" . $srch_bal->getQuery() . " $new_limit ) x");
        $left_wallet_bal = $db->fetch($rs_left_bal);
        $arr = $db->fetch_all($rs_listing);
        if (empty($arr)) {
            //resturn blank array in case Purchase history not found!
            return $this->prepareSuccessResponse($arr);
        }
        $balance = 0;
        foreach ($arr as $key => $row) {
            $particlr = convertLangTextToProperText($row['wh_particulars']);
            $ar = explode(":", $particlr);
            $str = $this->getTrimVal($ar[1]);
            $ptext = $ar[0] . ': ' . $str;

            $arr[$key]['balance'] = CONF_CURRENCY . number_format($left_wallet_bal['left_balance_onwards'], 2) . CONF_CURRENCY_RIGHT;
            $arr[$key]['wh_particulars'] = $ptext; //convertLangTextToProperText($row['wh_particulars']);  
            //$arr[$key]['wh_time'] = displayDate($row['wh_time'], true , true, $_SESSION['logged_user']['user_timezone']);
            $arr[$key]['wh_time'] = strtotime(displayDate($row['wh_time'], true, true, CONF_TIMEZONE));
            $arr[$key]['added'] = CONF_CURRENCY . number_format($row['added'], 2) . CONF_CURRENCY_RIGHT;
            $arr[$key]['used'] = CONF_CURRENCY . number_format($row['used'], 2) . CONF_CURRENCY_RIGHT;
            $arr[$key]['wh_amount'] = CONF_CURRENCY . number_format($row['wh_amount'], 2) . CONF_CURRENCY_RIGHT;
            $left_wallet_bal['left_balance_onwards'] -= $row['wh_amount'];
        }

        return $this->prepareSuccessResponse($arr);
    }

    private function getTrimVal($text)
    {
        preg_match_all('~>\K[^<>]*(?=<)~', $text, $match);
        if ($match[0][0]) {
            return $match[0][0];
        } else {
            return $text;
        }
    }

    public function printVoucher()
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Voucher List!');
        }
        require_once "./qrcode/qrlib.php";
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $voucher_id = array_key_exists('voucher_id', $request_data) ? $request_data['voucher_id'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $id = $voucher_id;
        $length = strlen($id);
        if ($length > 13) {
            $order_id = substr($id, 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($id, 13, $LastVouvherNo);
        } else {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        insertVoucherNumbers();
        $id = $voucher_id;
        $row_deal = [];
        $message = '';
        printVoucherDetail($id, $row_deal, $message, 'user');
        echo emailTemplate($message);
        die();
        if (!isset($message) || $message === null || strlen($message) < 10) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        //$str= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body >';
        //$str.= emailTemplate($message);
        //$str.= '</body></html>';
        echo emailTemplate($message);
        //	return $this->prepareSuccessResponse($message);
    }

}

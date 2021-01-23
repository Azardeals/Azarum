<?php  
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
require_once "../qrcode/qrlib.php";
if(!isRepresentativeUserLogged()) redirectUser(CONF_WEBROOT_URL.'representative/login.php');
$rep_id = $_SESSION['logged_user']['rep_id']; 

$rsc=$db->query("SELECT  *  FROM `tbl_companies` WHERE  company_rep_id=$rep_id ");
	$companyArray=array();
	while($arrs=$db->fetch($rsc)){

		$companyArray[$arrs['company_id']]= $arrs['company_id'];
	}

#$order_id = explode('-',$_GET['id']);
$id = $_GET['id'];
$length = strlen($id);
	if($length > 13){
		$order_id = substr($id, 0, 13);
		$LastVouvherNo = ($length-13);
		$voucher_no = substr($id, 13, $LastVouvherNo);
	}else{
		$order_id = $_GET['id'];
	}

	
/*   ------ Insert voucher number -------- */
		$srchVoucher=new SearchBase('tbl_order_deals', 'od');
        
        $srchVoucher->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status=1', 'o');
		$srchVoucher->addMultipleFields(array('o.order_id','od_deal_id','o.order_date',
        'od_deal_price', 'od_qty','od_gift_qty','od_voucher_suffixes'));
        $rsVoucher=$srchVoucher->getResultSet();
        
		while($row_voucher=$db->fetch($rsVoucher)){
		/* $total_qty += $row_voucher['od_qty']+$row_voucher['od_gift_qty'];
		$price = $row_voucher['od_deal_price'];
		  */
		
		$od_voucher_suffixes = explode(', ',$row_voucher['od_voucher_suffixes']);
		 
		
		foreach ($od_voucher_suffixes as $voucher){
		$voucher_id = $row_voucher['order_id'];
		$deal_id = $row_voucher['od_deal_id'];
	    $db->query("insert IGNORE into tbl_coupon_mark(cm_order_id,cm_counpon_no,cm_status,cm_deal_id) values('$voucher_id','$voucher','0','$deal_id')");  
		 
		}
		}
		/*   ------ Insert voucher number End Here -------- */

			$srch=new SearchBase('tbl_orders', 'o');
         /*  $srch->addCondition('o.order_user_id', '=', $_SESSION['logged_user']['user_id']); */
		  $srch->addCondition('o.order_id', '=', $order_id);
          
          $srch->joinTable('tbl_order_deals', 'INNER JOIN', "o.order_id=od.od_order_id and od.od_voucher_suffixes LIKE '%".$voucher_no."%'", 'od');
		  $srch->joinTable('tbl_users', 'INNER JOIN', "u.user_id=o.order_user_id ", 'u');
          
          $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
		  $srch->addCondition('d.deal_company','IN',$companyArray);
		  
		  $srch->joinTable('tbl_companies', 'INNER JOIN', 'c.company_id=d.deal_company', 'c');
		  $srch->joinTable('tbl_countries', 'INNER JOIN', 'ct.country_id=c.company_country', 'ct');
		  $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'ca.company_address_id=od.od_company_address_id ', 'ca');
          
 
          
          $srch->addOrder('order_date', 'desc');
 
		  
          
          
          
          $srch->addMultipleFields(array('o.*', 'od.*', 'd.*', 'c.*', 'ca.*', 'ct.*', 'u.*'));
        //  echo $srch->getQuery();
          
          
          $rs_listing=$srch->getResultSet();
		  while($row_deal=$db->fetch($rs_listing)){
			    
            if($row_deal['od_gift_qty']>0){
				$recipient = $row_deal['od_to_name']; 
				$email = $row_deal['od_to_email']; 
			}else{
				$recipient = $row_deal['user_name']; 
				$email = $row_deal['user_email']; 
			}  

			if(($row_deal['od_qty'])>0){
			   $order_id = $row_deal['order_id'].$voucher;
			}else if(($row_deal['od_gift_qty'])>0){
			  $order_id = $row_deal['order_id'].$voucher;
			}else{
			  $order_id = $row_deal['order_id'].$voucher;
			}
			
			if($row_deal['od_to_name'] == ''){
				$od_to_name = $row_deal['user_name'];
			}else{
				$od_to_name = $row_deal['od_to_name'];
			}
			
			if($row_deal['od_to_email'] == ''){
				$od_to_email = $row_deal['user_email'];
			}else{
				$od_to_email = $row_deal['od_to_email'];
			}	
			
			/* QR CODE */
			$PNG_TEMP_DIR = '../qrcode/temp'.DIRECTORY_SEPARATOR;
			//html PNG location prefix
			$PNG_WEB_DIR = CONF_WEBROOT_URL.'qrcode/temp/';
			if (!file_exists($PNG_TEMP_DIR))
				mkdir($PNG_TEMP_DIR);
			 
			$errorCorrectionLevel = 'L';
			$matrixPointSize = 4;
			$filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
			if(CONF_QR_CODE == 1){
				QRcode::png($id, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
				$officeUse = '';
			}
			if(CONF_QR_CODE == 2){
				QRcode::png('http://'.$_SERVER['SERVER_NAME'].CONF_WEBROOT_URL.'merchant/voucher-detail.php?id='.$id, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
				$officeUse = t_lang('M_TXT_FOR_OFFICE_USE_ONLY');
			}
			/* QR CODE */


		if(displayDate($row_deal['deal_tipped_at'])!=''){		
			$rs=$db->query("select * from tbl_email_templates where tpl_id=1");
			$row_tpl=$db->fetch($rs);	
			
			$subdealname = "";
			$deal_desc = '';
			$deal_name='';
			$style = 'style="color:#000; padding:3px 0;"';
			if($row_deal['od_sub_deal_name']!=""){
				$sub_deal_name= "(".$row_deal['od_sub_deal_name'].")";
			}	
			$date = "";
			if ($row_deal['obooking_booking_from'] != "" && $row_deal['obooking_booking_till'] != "") {

				$checkoutDate = date('Y-m-d', strtotime($row_deal['obooking_booking_till'] . ' +1 day'));
				$date = date("D M j Y", strtotime($row_deal['obooking_booking_from'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D M j Y", strtotime($checkoutDate));
				$date1 = strtotime($row_deal['obooking_booking_from']);
				$date2 = strtotime($checkoutDate);
				$diff = $date2 - $date1;
				$date .= " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
			}			
			
			$deal_name = html_entity_decode(appendPlainText($row_deal['deal_name' . $_SESSION['lang_fld_prefix']])).' '.$sub_deal_name;
			$deal_desc = '<li '.$style.'><strong>'.$deal_name.'</strong></li>';
			if($date != ''){
				$deal_desc .= '<li '.$style.'><strong>'.$date.'</strong></li>';
			}
			if($row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] != ''){
				$deal_desc .= '<li '.$style.'><strong>'.$row_deal['deal_desc' . $_SESSION['lang_fld_prefix']].'</strong></li>';
			}			
			
			
			
			$arr_replacements=array(
				'xxuser_namexx' =>  $row_deal['user_name'],
				'xxuser_identity_cardxx' =>  $row_deal['user_identity_card'],
				'xxuser_member_idxx' =>  $row_deal['user_member_id'],
				'xxdeal_namexx' => $deal_name,
				'xxamountxx' =>   CONF_CURRENCY . number_format($row_deal['od_deal_price']) . CONF_CURRENCY_RIGHT,
				'xxordered_coupon_qtyxx'=>'1',
				'xxinstructionsxx' =>($row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] ? $row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] : 'N/A'),
				'xxdeal_highlightsxx' => $row_deal['deal_highlights'],
				'xxdeal_descriptionxx' => $deal_desc,
				'xxcompany_namexx' => $row_deal['company_name'],
				'xxcompany_addressxx' => $row_deal['company_name'].'<br/>
				  '.$row_deal['company_address_line1'].',<br/>
				  '.$row_deal['company_address_line2'].'<br/>
				  '.$row_deal['company_address_line3'].' '.$row_deal['company_city'].' <br/>
				  '.$row_deal['company_state'].' '.$row_deal['country_name'].'<br/>',
				'xxcompany_zipxx' => $row_deal['company_address_zip'],
				'xxcompany_phonexx' => $row_deal['company_phone'],
				'xxcompany_emailxx' => $row_deal['company_email'],
				'xxrecipientxx' => $od_to_name,
				'xxemail_addressxx' => $od_to_email,
				'xxpurchase_datexx' => displayDate($row_deal['order_date'],true),
				'xxvalidtillxx' => displayDate($row_deal['voucher_valid_till']),
				'xxvalidfromxx' => displayDate($row_deal['voucher_valid_from']),
				'xxsite_namexx' => CONF_SITE_NAME,
				'xxserver_namexx'=>$_SERVER['SERVER_NAME'],
				'xxwebrooturlxx'=>CONF_WEBROOT_URL,
				'xxsite_urlxx'=>'http://'.$_SERVER['SERVER_NAME'].CONF_WEBROOT_URL,
				'xxname_of_userxx' => $row_deal['to_name'],
				'xxname_of_dealxx' => $row_deal['deal_name'],
				'xxwebsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
				'xxordered_coupon_qtyxx' => '1',
				/* 'xxorderidxx' => $row_deal['order_id'].$voucher, */
				'xxorderidxx' => $id,
				'xxqrcodexx'=>'<img src="' . $PNG_WEB_DIR.basename($filename) . '" />',
				'xxofficeusexx'=>$officeUse,
				'xxsitenamexx' => CONF_SITE_NAME
				);	
		}else{
			$rs=$db->query("select * from tbl_email_templates where tpl_id=7");
			$row_tpl=$db->fetch($rs);	
				
			$subdealname = "";
			$deal_desc = '';
			$deal_name='';
			$style = 'style="color:#000; padding:3px 0;"';
			if($row_deal['od_sub_deal_name']!=""){
				$sub_deal_name= "(".$row_deal['od_sub_deal_name'].")";
			}	
			$date = "";
			if ($row_deal['obooking_booking_from'] != "" && $row_deal['obooking_booking_till'] != "") {

				$checkoutDate = date('Y-m-d', strtotime($row_deal['obooking_booking_till'] . ' +1 day'));
				$date = date("D M j Y", strtotime($row_deal['obooking_booking_from'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D M j Y", strtotime($checkoutDate));
				$date1 = strtotime($row_deal['obooking_booking_from']);
				$date2 = strtotime($checkoutDate);
				$diff = $date2 - $date1;
				$date .= " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
			}			
			
			$deal_name = html_entity_decode(appendPlainText($row_deal['deal_name' . $_SESSION['lang_fld_prefix']])).' '.$sub_deal_name;
			$deal_desc = '<li '.$style.'><strong>'.$deal_name.'</strong></li>';
			if($date != ''){
				$deal_desc .= '<li '.$style.'><strong>'.$date.'</strong></li>';
			}
			if($row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] != ''){
				$deal_desc .= '<li '.$style.'><strong>'.$row_deal['deal_desc' . $_SESSION['lang_fld_prefix']].'</strong></li>';
			}				
				
			$arr_replacements=array(
				'xxuser_namexx' =>  $row_deal['user_name'],
				'xxdeal_namexx' => appendPlainText($deal_name),
				'xxdeal_descriptionxx' => $deal_desc,
				'xxis_giftedxx' => '',
				'xxamountxx' =>   CONF_CURRENCY . number_format($row_deal['od_deal_price'],2) . CONF_CURRENCY_RIGHT,
				'xxfriendxx'=>$row_deal['od_to_name'],
				'xxuser_identity_cardxx' =>  $row_deal['user_identity_card'],
				'xxuser_member_idxx' =>  $row_deal['user_member_id'],
				'xxmessagexx'=> $row_deal['od_email_msg'],
				'xxtippedxx'=>'The deal has not been tipped yet. You will notified by email when the deal is tipped.',
				'xxordered_coupon_qtyxx'=>'1',
				'xxinstructionsxx' => ($row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] ? $row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] : 'N/A'),
				/* 'xxorderidxx' => $row_deal['order_id'].$voucher, */'xxorderidxx' => $id,
				'xxdeal_highlightsxx' => $row_deal['deal_highlights' . $_SESSION['lang_fld_prefix']],
				'xxcompany_namexx' => $row_deal['company_name'],
				'xxcompany_addressxx' => $row_deal['company_name'].'<br/>
				  '.$row_deal['company_address_line1'].',<br/>
				  '.$row_deal['company_address_line2'].'<br/>
				  '.$row_deal['company_address_line3'].' '.$row_deal['company_city'].' <br/>
				  '.$row_deal['company_state'].' '.$row_deal['country_name'].'<br/>',
				'xxcompany_zipxx' => $row_deal['company_address_zip'],
				'xxcompany_phonexx' => $row_deal['company_phone'],
				'xxcompany_emailxx' => $row_deal['company_email'],
				'xxrecipientxx' => $row_deal['user_name'],
				'xxemail_addressxx' => $row_deal['user_email'],
				'xxpurchase_datexx' => displayDate($row_deal['order_date'],true),
				'xxvalidtillxx' => displayDate($row_deal['voucher_valid_till']),
				'xxvalidfromxx' => displayDate($row_deal['voucher_valid_from']),
				'xxsite_namexx' => CONF_SITE_NAME,
				'xxserver_namexx'=>$_SERVER['SERVER_NAME'],
				'xxwebrooturlxx'=>CONF_WEBROOT_URL,
				'xxqrcodexx'=>'<img src="' . $PNG_WEB_DIR.basename($filename) . '" />',
				'xxofficeusexx'=>$officeUse,
				'xxsite_urlxx'=>'http://'.$_SERVER['SERVER_NAME'].CONF_WEBROOT_URL
			);	
		}
		$message=$row_tpl['tpl_message'];
		$subject=$row_tpl['tpl_subject'];
		
		
		
		foreach ($arr_replacements as $key=>$val){
			$subject=str_replace($key, $val, $subject);
			$message=str_replace($key, $val, $message);
		}
		  		  


		  
  
		  
		   
		  }
		  if(!isset($message) || $message === null || strlen($message) < 10){
			$msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
			if(isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == "") redirectUser('tipped-members.php');
			redirectUser($_SERVER['HTTP_REFERER']);
		}
echo '<html><body >';?>
<script src="<?php echo CONF_WEBROOT_URL;?>js/jquery-1.4.2.js" type="text/javascript"></script>
<style>.box {
    background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #E1DEDE;
    box-shadow: 1px 2px 3px #9E9E9E;
    margin-bottom: 15px;
}
.box .title {
    background: -moz-linear-gradient(center top , #F9F9F9, #F1F1F1) repeat scroll 0 0 transparent;
    border-bottom: 1px solid #E1DEDE;
    border-radius: 3px 3px 0 0;
    box-shadow: 0 3px 3px #EDEDED;
    color: #2E2E2E;
    font-family: 'OpenSansSemibold';
    font-size: 18px;
    font-weight: bold;
    padding: 8px 15px;
    text-shadow: 0 1px 0 #FFFFFF;
    text-transform: uppercase;
}
.box .title-msg {
    background: -moz-linear-gradient(center top , #E56600, #FF700F) repeat scroll 0 0 transparent;
    border-bottom: 1px solid #E1DEDE;
    border-radius: 3px 3px 0 0;
    box-shadow: 0 3px 3px #EDEDED;
    color: #FFFFFF;
    font-family: 'OpenSansSemibold';
    font-size: 18px;
    font-weight: bold;
    padding: 8px 15px;
    text-shadow: 0 1px 0 #FFFFFF;
    text-transform: uppercase;
}
a.btn {
	text-transform: uppercase;
	margin:2px 2px;
	background: #eaeaea;
	background: -webkit-gradient(linear, left top, left bottom, from(#f7f7f7), to(#eaeaea));
	background: -moz-linear-gradient(top, #f7f7f7, #eaeaea);
 filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#f7f7f7', endColorstr='#eaeaea');
	-o-border-radius:3px;
	-icab-border-radius:3px;
	-khtml-border-radius:3px;
	-moz-border-radius:3px;
	-webkit-border-radius:3px;
	border-radius:3px;
	padding:3px 8px;
	width:auto;
	text-align: center;
	display: inline-block;
	font-size:12px;
	color:#8a8787;
	text-shadow:0px 1px 0px #fff;
	border:solid 1px #d7d7d7;
	text-decoration:none;
	float:right;
}
a.gray {
		background:-moz-linear-gradient(top, #595959 0%, #373737 100%); /* FF3.6+ */
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #595959), color-stop(100%, #373737)); /* Chrome,Safari4+ */
	background:-webkit-linear-gradient(top, #595959 0%, #373737 100%); /* Chrome10+,Safari5.1+ */
	background:-o-linear-gradient(top, #595959 0%, #373737 100%); /* Opera11.10+ */
	background:-ms-linear-gradient(top, #595959 0%, #373737 100%); /* IE10+ */
	background:linear-gradient(top, #595959 0%, #373737 100%); /* W3C */
 filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#595959', endColorstr='#373737');
	border:solid 1px #595959;
	border-bottom:solid 1px #282828;
	text-shadow:0px 1px 0px #292929;
	color:#fff;
}</style>
<?php 
if($_GET['used']!=""){
		$id = $_GET['used'];
		$length = strlen($id);
		if($length > 13){
			$order_id = substr($id, 0, 13);
			$LastVouvherNo = ($length-13);
			$voucher_no = substr($id, 13, $LastVouvherNo);
		}else{
			$order_id = $_GET['id'];
		}
		
		$srch = new SearchBase('tbl_coupon_mark', 'cm');
		$srch->addCondition('cm_order_id','=',$order_id);
		$srch->addCondition('cm_counpon_no','=',$voucher_no);
		$result = $srch->getResultSet();
		$row1=$db->fetch($result);
		$cm_id =  $row1['cm_id'];
		
		/* get records from db */
		$srch = new SearchBase('tbl_coupon_mark', 'cm');
		$srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
		
		$srch->addCondition('cm_order_id','=',$order_id);
		$srch->addCondition('cm_counpon_no','=',$voucher_no);
		$srch->addCondition('order_payment_status','>',0);
		$srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id ', 'd');
		$srch->addCondition('d.deal_company','IN',$companyArray);

		$srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
		$srch->addFld('CASE WHEN d.voucher_valid_from <= now()   THEN 1 ELSE 0 END as canUse');
		$srch->addFld('CASE WHEN  d.voucher_valid_till >= now() and cm.cm_status=0 THEN 1 ELSE 0 END as active');
		$srch->addFld('CASE WHEN  cm.cm_status=1 THEN 1 ELSE 0 END as used');
		$srch->addFld('CASE WHEN  (d.voucher_valid_till < now()  and cm.cm_status=0) || cm.cm_status=2  THEN 1 ELSE 0 END as expired');
		 

		$srch->addMultipleFields(array('od.od_order_id', 'od.od_to_name', 'u.user_name', 'u.user_email', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status','o.order_payment_capture', 'cm.cm_counpon_no','cm.cm_status','cm.cm_id','d.deal_id','d.deal_instant_deal','d.voucher_valid_from','d.voucher_valid_till'));
 

		$srch->addOrder('o.order_date', 'desc');
		$result = $srch->getResultSet();
		//echo $srch->getQuery();
		$row=$db->fetch($result);
		if($row['active'] == 1){
					if($row['canUse'] == 1){
						voucherUsed($cm_id);
					}else{
						$msg->addError(t_lang('M_MSG_VOUCHER_IS_NOT_ACTIVE_TO_USE'));
					}
		}
		 
		redirectUser('voucher-detail.php?id='.$_GET['used']);
	}
		
		$id = $_GET['id'];
		$length = strlen($id);
		if($length > 13){
			$order_id = substr($id, 0, 13);
			$LastVouvherNo = ($length-13);
			$voucher_no = substr($id, 13, $LastVouvherNo);
		}else{
			$order_id = $_GET['id'];
		}
		$srch = new SearchBase('tbl_coupon_mark', 'cm');
		$srch->addCondition('cm_order_id','=',$order_id);
		$srch->addCondition('cm_counpon_no','=',$voucher_no);
		$srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
		$srch->addCondition('order_payment_status','>',0);
		$srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id ', 'd');
		$srch->addCondition('d.deal_company','IN',$companyArray);

		$srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
		$srch->addFld('CASE WHEN d.voucher_valid_from <= now()   THEN 1 ELSE 0 END as canUse');
		$srch->addFld('CASE WHEN  d.voucher_valid_till >= now() and cm.cm_status=0 THEN 1 ELSE 0 END as active');
		$srch->addFld('CASE WHEN  cm.cm_status=1 THEN 1 ELSE 0 END as used');
		$srch->addFld('CASE WHEN  (d.voucher_valid_till < now()  and cm.cm_status=0) || cm.cm_status=2  THEN 1 ELSE 0 END as expired');
		 

		$srch->addMultipleFields(array('od.od_order_id', 'od.od_to_name', 'u.user_name', 'u.user_email', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status','o.order_payment_capture', 'cm.cm_counpon_no','cm.cm_status','cm.cm_id','d.deal_id','d.deal_instant_deal','d.voucher_valid_from','d.voucher_valid_till'));
 

		$srch->addOrder('o.order_date', 'desc');
		$result = $srch->getResultSet();
		 
		$row=$db->fetch($result);   
 echo  '<table cellspacing="0" cellpadding="0" border="0" bgcolor="#5894cd" align="center" width="900">

		<tbody><tr>
		  <td>';
		  if( (isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0])) ){ ?> 
				<div class="box" id="messages">
                    <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES');?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE');?></a></div>
                    <div class="content">
                      <?php if(isset($_SESSION['errs'][0])){?>
                      <div class="redtext"><?php echo $msg->display();?> </div>
                      <br/>
                      <br/>
					  <?php } 
					  if(isset($_SESSION['msgs'][0])){ 
					  ?>
                      <div class="greentext"> <?php echo $msg->display();?> </div>
                       <?php } ?>
                    </div>
                  </div>
				 <?php }
		  if($row['active'] == 1){
			if($row['canUse'] == 1){
				echo '<a href="?used='.$_GET['id'].'" class="btn gray">'.t_lang('M_TXT_MARK_USED').'</a>';
			}else{
				echo '<a   href="javascript:void(0);" onclick="alert(\'' . t_lang('M_MSG_VOUCHER_IS_NOT_ACTIVE_TO_USE') . '\')" class="btn gray">'.t_lang('M_TXT_MARK_USED').'</a> '; 
			}
		}
		 echo '</td></tr></table>'.emailTemplate($message);
 echo '</body></html>';
		?> 
		

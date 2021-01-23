<?php        
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
 

$post=getPostedData();
$get=getQueryStringData();
$mode=(isset($post['mode']))?$post['mode']:$get['mode'];

switch(strtoupper($mode)){
		
		case 'DISAPPROVEUSER' :
			$company_id = $post['company_id'];
			if(canDeleteCompany($company_id) == 0){
				$db->query("UPDATE tbl_companies set company_active = 0 WHERE company_id =$company_id");
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

				$headers .= 'From: ' . CONF_SITE_NAME . ' <' . CONF_EMAILS_FROM . '>' . "\r\n";
				$rsCompany=$db->query("select * from tbl_companies where company_id=$company_id");
				$row=$db->fetch($rsCompany);
				
				$rs=$db->query("select * from tbl_email_templates where tpl_id=37");
				$row_tpl=$db->fetch($rs);
				
				$message=$row_tpl['tpl_message'.$_SESSION['lang_fld_prefix']];
				$subject=$row_tpl['tpl_subject'.$_SESSION['lang_fld_prefix']];
				$arr_replacements=array(
				'xxcompany_namexx' => $row['company_name'],                   
				'xxuser_namexx' => $row['company_email'], 
				'xxemail_addressxx' => $row['company_email'],                    
				'xxsite_namexx' => CONF_SITE_NAME,
				'xxserver_namexx'=>$_SERVER['SERVER_NAME'],
				'xxwebrooturlxx'=>CONF_WEBROOT_URL,
				'xxsite_urlxx'=>'http://'.$_SERVER['SERVER_NAME'].CONF_WEBROOT_URL
				);
				
				foreach ($arr_replacements as $key=>$val){
					$subject=str_replace($key, $val, $subject);
					$message=str_replace($key, $val, $message);
				}
				
				if($row_tpl['tpl_status'] == 1){ 
					 
					mail($row['company_email'], $subject, emailTemplateSuccess($message), $headers);
				}
				
				echo '1';
			}else{
				echo '0';
			}
			
		break;
		
		case 'APPROVEUSER' :
			$company_id = $post['company_id'];	
			$db->query("UPDATE tbl_companies set company_active = 1 WHERE company_id =$company_id");
			echo '1';
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			$headers .= 'From: ' . CONF_SITE_NAME . ' <' . CONF_EMAILS_FROM . '>' . "\r\n";
			$rsCompany=$db->query("select * from tbl_companies where company_id=$company_id");
			$row=$db->fetch($rsCompany);
			
			$rs=$db->query("select * from tbl_email_templates where tpl_id=36");
			$row_tpl=$db->fetch($rs);
			
			$message=$row_tpl['tpl_message'.$_SESSION['lang_fld_prefix']];
			$subject=$row_tpl['tpl_subject'.$_SESSION['lang_fld_prefix']];
			$arr_replacements=array(
			'xxcompany_namexx' => $row['company_name'],                   
			'xxuser_namexx' => $row['company_email'], 
			'xxemail_addressxx' => $row['company_email'],                    
			'xxsite_namexx' => CONF_SITE_NAME,
			'xxserver_namexx'=>$_SERVER['SERVER_NAME'],
			'xxwebrooturlxx'=>CONF_WEBROOT_URL,
			'xxsite_urlxx'=>'http://'.$_SERVER['SERVER_NAME'].CONF_WEBROOT_URL
			);
			
			foreach ($arr_replacements as $key=>$val){
				$subject=str_replace($key, $val, $subject);
				$message=str_replace($key, $val, $message);
			}
			
			if($row_tpl['tpl_status'] == 1){ 
				 
				mail($row['company_email'], $subject, emailTemplateSuccess($message), $headers);
			}
		break;
		
		 

}

?>

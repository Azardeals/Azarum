<?php
	class messageLanguage extends Message
	{
		public function display($langMessage="")
		{
			if($_SESSION['lang_fld_prefix']=='_lang1'){
				/* $extMessage=$langMessage!=""?str_replace("The Following Errors Occured:",$langMessage,Message::getHtml()):Message::getHtml(); */
				$msg=new message();
				$extMessage= str_replace(CONF_MESSAGE_ERROR_HEADING,t_lang('M_TXT_THE_FOLLOWING_ERROR_OCCURED'),$msg->display()) ;
				
				return ($extMessage);
			}else{
				/* $extMessage=  Message::getHtml(); */
				$msg=new message();
				$extMessage=  $msg->display();
				return ($extMessage);
			}	
		}
		
		
    
   	}
	$pagename=substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4);
	$msg1=new messageLanguage();
	/* if($pagename == 'deal' || $pagename == 'deal-detail' || $pagename == 'cms-page' ){
		$msg1=new messageLanguage();
	}else{
		$msg=new messageLanguage();
	} */	
?>
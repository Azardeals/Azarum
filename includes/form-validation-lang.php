<?php 
/*
 * sample lang file for js form validation
 */
require_once __DIR__ . '/../application-top.php';

//echo "jQuery.Validation.setMessages({
	
echo "jQuery.Validation.setMessages({
	'required':'{caption}".' '. t_lang('M_JS_IS_MANDATORY')."',
	'charonly':'". t_lang('M_JS_ONLY_CHARACTERS_ARE_SUPPORTED_FOR')." {caption}',
	'integer':'".t_lang('M_JS_PLEASE_ENTER_INTEGER_VALUE_FOR')." {caption}',
	'floating':'".t_lang('M_JS_PLEASE_ENTER_NUMERIC_VALUE_FOR')." {caption}',
	'lengthrange':'".t_lang('M_JS_LENGTH_OF_CAPTION_RANGE')."',
	'range':'".t_lang('M_JS_VALUE_OF_CAPTION_RANGE')."',
	'username':'{caption} ".t_lang('M_JS_USERNAME_VALIDATION_MESSAGE')."',
	'password':'{caption} ".t_lang('M_JS_PASSWORD_VALIDATION_MESSAGE')."',
	'comparewith_eq':'{caption}  ".t_lang('M_JS_MUST_BE_SAME_AS')." {comparefield}',
	'comparewith_lt':'{caption}  ".t_lang('M_JS_MUST_BE_LESS_THAN')." {comparefield}',
	'comparewith_le':'{caption}  ".t_lang('M_JS_MUST_BE_LESS_THAN_OR_EQUAL_TO')." {comparefield}',
	'comparewith_gt':'{caption}  ".t_lang('M_JS_MUST_BE_GREATER_THAN')." {comparefield}',
	'comparewith_ge':'{caption}  ".t_lang('M_JS_MUST_BE_GREATER_THAN_OR_EQUAL_TO')." {comparefield}',
	'comparewith_ne':'{caption}  ".addslashes(t_lang('M_JS_SHOULD_NOT_BE_SAME_AS'))." {comparefield}',
	'email':'".t_lang('M_JS_EMAIL_VALIDATION_MESSAGE')." ',
	'user_regex':'".t_lang('M_JS_REGULAREXPRESSION_VALIDATION_MESSAGE')." {caption}'
    });";
?>

function checkUnique(fld, tbl, tbl_fld, tbl_key, key_fld, constraints){
	
   fld.addClass('field-processing');
    var entered = fld.val();
	
	validEnterd = entered.replace(/ /g, '').length;
	entered = entered.trim();
	if(validEnterd >0) {
		
		$.ajax({
			url: webroot + 'check-unique.php',
			type: 'POST',
			dataType: 'json',
			data: {'val':entered, 'tbl':tbl, 'tbl_fld':tbl_fld, 'tbl_key':tbl_key, 'key_val':key_fld.val(), 'constraints':constraints},
			success: function(ans){
				fld.removeClass('field-processing');
				$(fld).attr('data-mbsunichk', 1);
				if(ans.status==0){
					if(entered.length < 1){
						alert(fld.attr('title') + " <?php echo t_lang('M_JS_IS_MANDATORY');?>");
					}
					
					
					fld.addClass('error');
					$(".erlist_email").hide();
					fld.after('<ul class="errorlist erlist_email"><li><a href="javascript:void(0);">'+fld.attr('title')+' <?php echo t_lang('M_ERROR_ALREADY_EXIST');?></a></li></ul>');
					fld.focus();
				}else {
					$(".erlist_email").hide();
					fld.removeClass('error');
					
				}
			}
		});
	
	}
}
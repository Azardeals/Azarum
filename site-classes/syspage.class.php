<?php
class Syspage{
	static function addJs($file, $common = false){
	    if (is_array($file)){
	        foreach ($file as $fl){
	            self::addJs($fl, $common);
	        }
	        return ;
	    }
	    if ($common){
	        global $arr_page_js_common;
	        if (!in_array($file, $arr_page_js_common)) $arr_page_js_common[] = $file;
	    }
	    else {
	        global $arr_page_js;
	        if (!in_array($file, $arr_page_js)) $arr_page_js[] = $file;
	    }
	}
	
	static function addCss($file, $common = false){
	    if (is_array($file)){
	        foreach ($file as $fl){
	            self::addCss($fl, $common);
	        }
	        return ;
	    }
	    if ($common){
	        global $arr_page_css_common;
	        if (!in_array($file, $arr_page_css_common)) $arr_page_css_common[] = $file;
	    }
	    else {
	        global $arr_page_css;
	        if (!in_array($file, $arr_page_css)) $arr_page_css[] = $file;
	    }
	}
	
	static function getPostedVar($key=null){
	    global $post;
	    if ($key == null) return $post;
	    return $post[$key];
	}
	
	static function getJsCssIncludeHtml($merge_files = true){
	    global $arr_page_css_common;
	    global $arr_page_css;
	    global $arr_page_js_common;
	    global $arr_page_js;
	    
	    global $tpl_for_js;
	    global $tpl_for_css;
	    
	    $str = '';
	    
	    $use_root_url = CONF_WEBROOT_URL;
	    
	    if (count($arr_page_css_common) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_css_common as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files){
	                $str .= '<link rel="stylesheet" type="text/css" href="'.CONF_WEBROOT_URL.'css.php?f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '" />' . "\n";
	            }
	        }
	        
	        if ($merge_files) $str .= '<link rel="stylesheet" type="text/css" href="'.CONF_WEBROOT_URL.'css.php?f=' . rawurlencode(implode(',', $arr_page_css_common)) . '&min=1&sid=' . $last_updated . '" />' . "\n";
	    }
	    
	    if (count($arr_page_css) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_css as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<link rel="stylesheet" type="text/css" href="'.CONF_WEBROOT_URL.'css.php?f='. rawurlencode($val) . '&min=0&sid=' . $last_updated . '" />' . "\n";
	        }
	        if ($merge_files) $str .= '<link rel="stylesheet" type="text/css" href="'.CONF_WEBROOT_URL.'css.php?f='. rawurlencode(implode(',', $arr_page_css)) . '&min=1&sid=' . $last_updated . '" />' . "\n";
			
	    }
	    
	    if ($tpl_for_css != ''){
	        $pathinfo = pathinfo($tpl_for_css);
	        $temp_pth = realpath( $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.css');
			
			$src = CONF_WEBROOT_URL.'css.php?f=' .CONF_WEBROOT_URL.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.css';
			
			if(strpos($temp_pth,'manager')!=FALSE){
				$src = CONF_WEBROOT_URL.'css.php?f=' .CONF_WEBROOT_URL.'manager/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.css';
			}
			
			if(strpos($temp_pth,'merchant')!=FALSE){
				$src = CONF_WEBROOT_URL.'css.php?f=' .CONF_WEBROOT_URL.'merchant/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.css';
			}
			
			if(strpos($temp_pth,'representative')!=FALSE){
				$src = CONF_WEBROOT_URL.'css.php?f=' .CONF_WEBROOT_URL.'representative/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.css';
			}
			
			if(strpos($temp_pth,'mobileversion')!=FALSE){
				$src = CONF_WEBROOT_URL.'css.php?f=' .CONF_WEBROOT_URL.'mobileversion/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.css';
			}
			
	        if ( file_exists($temp_pth) ){
	            $str .= '<link rel="stylesheet" type="text/css" href="'.$src. '&sid=' . filemtime($temp_pth) . '" />' . "\n";
	        }
	    }
	    
		$str .= '<script type="text/javascript">
			var webroot="' . CONF_WEBROOT_URL . '";
		</script>' . "\r\n";
	    
	    if (count($arr_page_js_common) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_js_common as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<script type="text/javascript" language="javascript" src="'.CONF_WEBROOT_URL.'js.php?f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '"></script>' . "\n"; 
	        }
	        if ($merge_files) $str .= '<script type="text/javascript" language="javascript" src="'.CONF_WEBROOT_URL.'js.php?f=' . rawurlencode(implode(',', $arr_page_js_common)) . '&min=1&sid=' . $last_updated . '"></script>' . "\n";
	    }
	     
	    if (count($arr_page_js) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_js as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<script type="text/javascript" language="javascript" src="'.CONF_WEBROOT_URL.'js.php?f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '"></script>' . "\n";
	        }
			if($merge_files)
	        $str .= '<script type="text/javascript" language="javascript" src="'.CONF_WEBROOT_URL.'js.php?f=' . rawurlencode(implode(',', $arr_page_js)) . '&min=1&sid=' . $last_updated . '"></script>' . "\n";
	    }
	    
	    if ($tpl_for_js != ''){
			$pathinfo = pathinfo($tpl_for_js);
	        $temp_pth = realpath( $pathinfo['dirname'].'/'.$pathinfo['filename'] . '.js');
	        if (file_exists($temp_pth)){
				$src = CONF_WEBROOT_URL.'js.php?f='.CONF_WEBROOT_URL.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.js';
				if(strpos($temp_pth,'manager')!=FALSE){
					$src = CONF_WEBROOT_URL.'js.php?f='.CONF_WEBROOT_URL.'manager/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.js';
				}
				if(strpos($temp_pth,'merchant')!=FALSE){
					$src = CONF_WEBROOT_URL.'js.php?f='.CONF_WEBROOT_URL.'merchant/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.js';
				}
				
				if(strpos($temp_pth,'representative')!=FALSE){
					$src = CONF_WEBROOT_URL.'js.php?f=' .CONF_WEBROOT_URL.'representative/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.js';
				}
				
				if(strpos($temp_pth,'mobileversion')!=FALSE){
					$src = CONF_WEBROOT_URL.'js.php?f=' .CONF_WEBROOT_URL.'mobileversion/'.$pathinfo['dirname'].'/'.$pathinfo['filename'].'.js';
				}
	            $str .= '<script type="text/javascript" language="javascript" src="'.$src. '&sid=' . filemtime($temp_pth) . '"></script>' . "\n";
	        }
	    }
	    return $str;
	}
}
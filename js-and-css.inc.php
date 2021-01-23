<script type="text/javascript">
    var webroot = '<?php echo CONF_WEBROOT_URL; ?>';
</script>
<?php

function addScriptHTML($arr_js, $min)
{
    $last_updated = 0;
    foreach ($arr_js as $val) {
        $temp_pth = (substr($val, 0, 1) == '/') ? $_SERVER['DOCUMENT_ROOT'] . $val : realpath($val);
        $time = filemtime($temp_pth);
        if ($time > $last_updated)
            $last_updated = $time;
        echo '<script type="text/javascript" src="' . CONF_WEBROOT_URL . 'js.php?f=' . rawurlencode($val) . '&amp;min=' . intval($min) . '&amp;sid=' . $time . '"></script>' . "\n";
    }
}

function addStyleCssHtml($arr_css)
{
    $last_updated = 0;
    foreach ($arr_css as $val) {
        $temp_pth = (substr($val, 0, 1) == '/') ? $_SERVER['DOCUMENT_ROOT'] . $val : realpath($val);
        $time = filemtime($temp_pth);
        if ($time > $last_updated) {
            $last_updated = $time;
        }
    }
    echo '<link rel="stylesheet" type="text/css" href="' . CONF_WEBROOT_URL . 'css.php?f=' . rawurlencode(implode(',', $arr_css)) . '&min=1&sid=' . $last_updated . '" />' . "\n";
}

if (is_array($arr_common_js) && count($arr_common_js) > 0) {
    addScriptHTML($arr_common_js, 1);
}
if (is_array($arr_page_js) && count($arr_page_js) > 0) {
    addScriptHTML($arr_page_js, 0);
}
if (is_array($arr_common_css) && count($arr_common_css) > 0) {
    addStyleCssHtml($arr_common_css);
}
if (is_array($arr_page_css) && count($arr_page_css) > 0) {
    addStyleCssHtml($arr_page_css);
}
if ((strpos($_SERVER['REQUEST_URI'], 'manager/') != false) || (strpos($_SERVER['REQUEST_URI'], 'merchant/') !== false ) || (strpos($_SERVER['REQUEST_URI'], 'representative/') !== false )) {
    echo '<script type="text/javascript" src="' . CONF_WEBROOT_URL . 'innova-lnk/scripts/innovaeditor.js "></script>';
    echo '<script type="text/javascript" src="' . CONF_WEBROOT_URL . 'innova-lnk/scripts/common/webfont.js "></script>';
}

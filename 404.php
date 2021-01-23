<?php
require_once './application-top.php';
require_once './header.php';
?>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel__centered">
                    <div class="sectioncenter">
                        <img class="errorimg" alt="error" src="<?php echo CONF_WEBROOT_URL . 'images/404error.png'; ?>">
                        <?php echo unescape_attr(t_lang('M_TXT_ERROR_CONTENT')); ?>
                        <div class="listpanel">
                            <ul>
                                <li><?php echo t_lang('M_TXT_INVALID_REQUEST') ?></li>
                                <li><?php echo t_lang('M_TXT_INCORRECT_PAGE_URL') ?></li>
                                <li><?php echo t_lang('M_TXT_PAGE_OR_FILE_LINK_IS_TIMED_OUT_AT_THIS_MOMENT') ?></li>
                            </ul>
                        </div>
                        <a href="<?php echo CONF_WEBROOT_URL; ?>" class="themebtn"><?php echo t_lang('M_TXT_BACK_TO_HOME'); ?></a>
                    </div>
                </div>
            </div>
        </div>    
    </div>    
</section>
<?php
require_once './footer.php';

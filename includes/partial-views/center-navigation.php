<?php require_once './application-top.php'; ?>
<div class="fixed_container">
    <ul>
        <li><a class="<?php echo ($pagename == 'faq') ? 'active' : '' ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'faq.php'); ?>"><?php echo t_lang('M_TXT_FAQS'); ?></a></li>
        <li><a class="<?php echo ($pagename == 'press') ? 'active' : '' ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'press.php'); ?>"><?php echo t_lang('M_TXT_PRESS'); ?></a></li>
        <li><a class="<?php echo ($pagename == 'jobs') ? 'active' : '' ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'jobs.php'); ?>"><?php echo t_lang('M_TXT_JOBS'); ?></a></li>
        <li><a class="<?php echo ($pagename == 'blog-listing') ? 'active' : '' ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-listing.php'); ?>"><?php echo t_lang('M_TXT_BLOG'); ?></a></li>
        <li><a class="<?php echo ($pagename == 'news') ? 'active' : '' ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'news.php'); ?>"><?php echo t_lang('M_TXT_NEWS'); ?></a></li>
    </ul>
</div>
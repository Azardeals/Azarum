<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
$rs1 = $db->query("select * from tbl_business_page");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['business_conf_name']), nl2br($row1['business_conf_value']));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Partner with Mondo Express</title>
        <link rel="stylesheet" type="text/css" href="<?php echo CONF_WEBROOT_URL; ?>css/business_style.css" />
        <script src="<?php echo CONF_WEBROOT_URL; ?>js/jquery-latest.js" type="text/javascript"></script>
    </head>
    <body style="background:url('<?php echo CONF_WEBROOT_URL; ?>images/backgroundBg.jpg') repeat scroll 0 0 transparent!important;">
        <!--wrapper start here-->
        <div id="wrapper">
            <!--header start here-->
            <div id="header">
                <div class="headerTop">
                    <div class="headerLeft">
                        <h1>
                            <?php if (CONF_FRONT_END_LOGO == "") { ?>
                                <a href="<?php echo CONF_WEBROOT_URL; ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/mondo-logo.png" border="0" alt="<?php echo CONF_SITE_NAME; ?>" title="<?php echo CONF_SITE_NAME; ?>" /></a>
                            <?php } else { ?>
                                <a href="<?php echo CONF_WEBROOT_URL; ?>"><img border="0" src="<?php echo LOGO_URL . CONF_FRONT_END_LOGO; ?>" alt="<?php echo CONF_SITE_NAME; ?>"></a>
                            <?php } ?>            </h1>
                    </div>
                    <div class="headerRight">
                        <h3><?php echo BUSINESS_HEADER_TEXT; ?></h3>
                        <a href="<?php echo friendlyUrl(CONF_WEBRROT_URL . 'suggest-a-business.php') ?>" class="yellow_apply"><span><?php echo t_lang('M_TXT_APPLY_NOW'); ?></span></a>          </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--header end here-->
            <!--bannerArea start here--> 
            <div id="bannerArea">
                <div id="bannerWrapper">
                    <div class="bannerLeft">
                        <div class="quotesWrapper">
                            <div class="fl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/quoteBg_top.png" alt="" /></div>
                            <div class="quotesWrap">
                                <h4><?php echo BUSINESS_QUOTE_TEXT; ?></h4>
                            </div>
                            <div class="fl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/quoteBg_bottom.png" alt="" /></div>
                        </div>
                        <h3><?php echo BUSINESS_QUOTE_NAME; ?></h3>
                    </div>
                    <div class="bannerGirl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/intro_girl.png" alt="" /></div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--bannerArea end here-->  
            <div class="clear"></div> 
            <!--body start here-->  
            <div id="body">
                <div id="bodyWrapper">
                    <div class="bodyHead">
                        <h2><?php echo BUSINESS_WHY_HEADING; ?></h2>
                        <a href="<?php echo friendlyUrl(CONF_WEBRROT_URL . 'suggest-a-business.php') ?>" class="yellow_apply fr"><span><?php echo t_lang('M_TXT_APPLY_NOW'); ?></span></a>
                    </div>
                    <div class="bodyContainer">
                        <!--containerWhite start here-->
                        <div class="containerWhite">
                            <p><?php echo BUSINESS_WHY_HEADING_TEXT; ?></p>
                            <div class="containerWhite_right">
                                <div class="roundBox_left">
                                    <h4><?php echo BUSINESS_BUILD_YOUR_PLAN; ?></h4>
                                    <a href="#brand" class="readMore"><?php echo t_lang('M_TEXT_READ_MORE'); ?></a>
                                </div>
                                <div class="roundBox_Mid">
                                    <h4><?php echo BUSINESS_FIND_MORE_CUSTOMER; ?></h4>
                                    <a href="#customer" class="readMore"><?php echo t_lang('M_TEXT_READ_MORE'); ?></a>
                                </div>
                                <div class="roundBox_Right">
                                    <h4><?php echo BUSINESS_BOOK_MORE_REVENUE; ?></h4>
                                    <a href="#revenue" class="readMore"><?php echo t_lang('M_TEXT_READ_MORE'); ?></a>
                                </div>
                            </div>
                        </div>
                        <!--containerWhite end here-->  
                        <a id="brand"></a>
                        <!--containerYellow start here-->  
                        <div class="containerYellow">
                            <img src="<?php echo CONF_WEBROOT_URL; ?>images/icon_build.png" alt="" class="sideGraphic" />
                            <div class="containerYellow_left">
                                <h3><?php echo BUSINESS_BUILD_YOUR_PLAN1; ?></h3>
                                <p><?php echo BUSINESS_BUILD_YOUR_BRAND_TEXT; ?></p>
                                <!--  <a href="#" class="showLink">Read more</a> -->
                            </div>
                            <div class="containerYellow_right">
                                <div class="circle"><?php echo BUSINESS_PERCENT_AMOUNT; ?></div>
                                <h4><?php echo BUSINESS_PERCENT_TEXT; ?></h4>
                            </div>
                        </div>
                        <!--containerYellow end here-->  
                        <a id="customer"></a>
                        <!--containerBlue start here-->   
                        <div class="containerBlue">
                            <div class="containerBlue_left">
                                <div class="quotesWrapper_blue">
                                    <span class="fl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/bluequote_top.jpg" alt="" /></span>
                                    <div class="quotesWrap_blue">
                                        <h3><?php echo BUSINESS_CUSTOMER_QUOTE_TEXT; ?></h3>
                                    </div>
                                    <span class="fl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/bluequote_bottom.jpg" alt="" /></span>
                                    <h4><?php echo BUSINESS_CUSTOMER_QUOTE_TEXT_BY; ?></h4>
                                </div>
                            </div>
                            <div class="containerBlue_right">
                                <h3><?php echo BUSINESS_FIND_MORE_CUSTOMER1; ?></h3>
                                <p><?php echo BUSINESS_FIND_MORE_CUSTOMER_TEXT; ?></p>
                                <!-- <a href="#" class="showLink">Read more</a> -->
                            </div>
                            <img src="<?php echo CONF_WEBROOT_URL; ?>images/icon_find.png" alt="" class="sideGraphicBlue" />
                        </div>
                        <!--containerBlue end here--> 
                        <!--containerGreen start here-->    
                        <div class="containerGreen">
                            <img src="<?php echo CONF_WEBROOT_URL; ?>images/icon_clock.png" alt="" class="sideGraphic" />
                            <div class="containerGreen_left">
                                <a id="revenue"></a>
                                <h3><?php echo BUSINESS_BOOK_MORE_REVENUE1; ?></h3>
                                <p><?php echo BUSINESS_BOOK_MORE_REVENUE_TEXT; ?></p>
                            </div>
                            <div class="containerGreen_right">
                                <h2><?php echo BUSINESS_PAST_DEALS_TEXT; ?></h2>
                                <ul class="listing_pastDeals">
                                    <li>
                                        <div class="dealsBox_wrap">
                                            <h5>Kushi <br /><span>Washington, D.C.</span></h5>
                                            <div class="dealsBox">
                                                <div class="dealsBoxTop">
                                                    <h3>4,321 <br /><span>vouchers sold</span></h3>
                                                </div>
                                                <div class="dealsBoxBottom">
                                                    <h3>$108,025 <br /><span>gross revenue</span></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dealsBox_wrap">
                                            <h5>Spa Scotta  <br /><span>Seattle, WA</span></h5>
                                            <div class="dealsBox">
                                                <div class="dealsBoxTop">
                                                    <h3>4,321 <br /><span>vouchers sold</span></h3>
                                                </div>
                                                <div class="dealsBoxBottom">
                                                    <h3>$108,025 <br /><span>gross revenue</span></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dealsBox_wrap">
                                            <h5>Happy Cakes <br /><span>Shop Denver, CO</span></h5>
                                            <div class="dealsBox">
                                                <div class="dealsBoxTop">
                                                    <h3>4,321 <br /><span>vouchers sold</span></h3>
                                                </div>
                                                <div class="dealsBoxBottom">
                                                    <h3>$108,025 <br /><span>gross revenue</span></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <span class="fl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/greenBottom.png" alt="" /></span>
                        <!--containerGreen end here--> 
                    </div>
                </div> 
                <div class="clear"></div>
            </div>
            <!--body end here-->  
            <!--footer start here-->
            <div id="footer">
                <div id="footerWrapper">
                    <span class="fl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/footBg_top.png" alt="" /></span>
                    <div class="footerWrap">
                        <div class="footerLeft">
                            <ul class="footerLinks">
                                <?php echo printNav(0, 1); ?>
                            </ul>
                            <p class="copy">&copy; <?php echo date("Y"); ?>, <?php echo t_lang('M_TXT_RIGHT_RESERVE'); ?></p>
                        </div>
                        <div class="footerRight">
                            <p class="signatures">
                                <a href="http://www.fatbit.com/" target="_blank">FATbit Technologies:</a>
                                <a href="http://www.fatbit.com/" target="_blank">Website Design Company</a>
                            </p>
                            <ul class="listingsocials">
                                <li><a href="#"><img src="<?php echo CONF_WEBROOT_URL; ?>images/facebook.png" border="0" /></a></li>
                                <li><a href="#"><img src="<?php echo CONF_WEBROOT_URL; ?>images/twitter.png" border="0" /></a></li>
                            </ul>
                        </div>
                    </div>
                    <span class="fl"><img src="<?php echo CONF_WEBROOT_URL; ?>images/footBg_bottom.png" alt="" /></span>
                </div>
                <div class="clear"></div>
            </div>
            <!--footer end here-->
        </div>
        <!--wrapper end here-->
    </body>
</html>

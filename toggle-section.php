<script type="text/javascript">
    $(document).ready(function () {
        //Set default open/close settings
        $('.togglecontent').hide(); //Hide/close all containers
        $('.togglehead:first').addClass('active').next().show(); //Add "active" class to first trigger, then show/open the immediate next container
        //On Click
        $('.togglehead').click(function () {
            if ($(this).next().is(':hidden')) { //If immediate next container is closed...
                $('.togglehead').removeClass('active').next().slideUp(); //Remove all .acc_trigger classes and slide up the immediate next container
                $(this).toggleClass('active').next().slideDown(); //Add .acc_trigger class to clicked trigger and slide down the immediate next container
            }
            return false; //Prevent the browser jump to the link anchor
        });
    });
</script>
<div class="sectionToggles">
    <span class="togglehead"><span class="txt"><?php echo unescape_attr(t_lang('M_TXT_LOCATION_CONTACT')); ?></span><span class="plus"></span></span>
    <div class="togglecontent"><?php echo EXTRA_LOCATION_CONTACT; ?></div>
    <script type="text/javascript">
        $('#founder-img').attr('src', '<?php echo CONF_WEBROOT_URL; ?>' + $('#founder-img').attr('src'));
        $('#co-founder-img').attr('src', '<?php echo CONF_WEBROOT_URL; ?>' + $('#co-founder-img').attr('src'));
    </script>					
    <span class="togglehead"><span class="txt">
            <a href="javascript:void(0);"><?php echo t_lang('M_TXT_FEATURED_JOBS'); ?></a>
        </span><span class="plus"></span>
    </span>
    <div class="togglecontent">
        <ul class="listingbullets">
            <?php
            $srch = new SearchBase('tbl_jobs', 'j');
            $srch->addCondition('j.jobs_status', '=', 1);
            $srch->addCondition('j.jobs_city_id', '=', $_SESSION['city']);
            $srch->joinTable('tbl_job_catagory', 'INNER JOIN', 'j.jobs_category=jc.job_category_id', 'jc');
            $srch->addGroupBy('jc.job_category_name');
            $srch->addOrder('jc.job_category_name');
            $srch->addOrder('j.jobs_title');
            $rs1 = $srch->getResultSet();
            $result = $srch->recordCount();
            if ($result > 0) {
                while ($row1 = $db->fetch($rs1)) {
                    echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'jobs-detail.php?id=' . $row1['jobs_id']) . '">' . $row1['jobs_title' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                }
            } else {
                echo "<li>" . t_lang("M_TXT_NO_RECORD_FOUND") . "</li>";
            }
            ?>
        </ul> 
    </div>
    <span class="togglehead"><span class="txt"><a  href="javascript:void(0);"><?php echo t_lang('M_TXT_PUBLIC_RELATIONS'); ?></a></span><span class="plus"></span></span>
    <div class="togglecontent">
        <ul class="listingbullets">
            <?php echo printNav(0, 4); ?>
        </ul>
    </div>    
</div>
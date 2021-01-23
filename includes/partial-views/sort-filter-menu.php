<?php require_once './application-top.php'; ?>
<ul class="grids__half list__inline right">
    <li><a class="link__filter themebtn themebtn--large" href="javascript:void(0)"><?php echo t_lang('M_TXT_FILTER'); ?></a></li>
    <li>
        <div class="sort siteForm">
            <select onchange="selectSort(this);">
                <option value=""><?php echo t_lang('M_TXT_SORT_BY'); ?></option>
                <option value="price||asc"><?php echo t_lang('M_TXT_PRICE_ASC'); ?></option>
                <option value="price||desc"><?php echo t_lang('M_TXT_PRICE_DESC'); ?></option>
                <option value="deal_end_time||Asc"><?php echo t_lang('M_TXT_DEAL_EXPIRY'); ?></option>
                <option value="deal_id||desc"><?php echo t_lang('M_TXT_LATEST_DEAL'); ?></option>
            </select>
        </div>
    </li>
</ul>

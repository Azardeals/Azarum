<?php extract($vars); ?>
<div class="footinfo">
    <aside class="grid_1">
        <ul class="pagination">
            <?php
            if ($page > 1) {
                echo '<li class="prev "><a href="javascript:void(0)" onclick="setPage(' . ($page - 1) . ',document.frmPaging)"></a></li>';
            }
            if ($pages > 1) {
                echo getPageString('<li><a  href="javascript:void(0)" onclick="setPage(xxpagexx,document.frmPaging)">xxpagexx</a></li>', $pages, $page, ' <li class="selected"><a class="active" href="javascript:void(0)">xxpagexx</a></li>', '<li class="more disabled "><a href="javascript:void(0);"></a></li>');
            }
            if ($page < $pages) {
                echo '<li class="next"><a href="javascript:void(0)" onclick="setPage(' . ($page + 1) . ',document.frmPaging)"></a></li>';
            }
            ?>      
        </ul>
    </aside>  
    <aside class="grid_2">
        <span class="info">
            <?php echo t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) . ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $total_records) ? $total_records : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $total_records ?>
        </span>
    </aside>
</div>
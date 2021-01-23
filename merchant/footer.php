</tr>
</table>
</div></td>
</tr>
<tr>
    <td id="footer">
        <p><?php echo t_lang('M_TXT_NOTE_SERVER_TIME') . ' ' . addTimezone(date("l M d, Y, H:i"), CONF_TIMEZONE); ?></p>
        <p class="signature">
			<?php echo t_lang('M_TXT_POWERED_BY');
            echo ' ' . CONF_POWERED_BY; ?>
		</p>
	</td>
</tr>
</table>
</div>
<!-- Below is the script to enable the click on iPad and hide the Left Toggled menu on click outside anywhere -->
<script>
    $(document).on('touchstart', function (event) {
        if ($(".menutrigger").hasClass("active")) {
            $(".menutrigger").removeClass("active");
            $("body").removeClass("toggled-left");
        }
    });
</script>
</body>
</html>
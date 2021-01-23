<?php
$html = Loader::helper('html');
$this->addHeaderItem($html->javascript('jscalendar/calendar.js'));
$this->addHeaderItem($html->javascript('jscalendar/lang/calendar-en.js'));
$this->addHeaderItem($html->javascript('jscalendar/calendar-setup.js'));
$this->addHeaderItem($html->css('calendar-win2k-1.css'));
?>
<script src="<?php echo BASE_URL . DIR_REL; ?>/js/lib.js" type="text/javascript"></script>
<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['contact_frm'])) {
        $name = $_POST['name_app'];
        $profession = $_POST['profession_app'];
        $experience = $_POST['experience_app'];
        $city = $_POST['city_app'];
        $email = $_POST['email_app'];
        $phone = $_POST['phone_app'];
        $mobile = $_POST['mobile_app'];
        $date = $_POST['date_app'];
        $time = $_POST['time_app'];
        $confirm = $_POST['confirm_app'];
        $message = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
			<td height="30" bgcolor="#EEEEEE" ><strong><font style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:11px; color:#333333">Contact Us Submission Form</font></strong></td>
		  </tr>
		  <tr>
			<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">Your Name :</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $name . '</font></td>
			  </tr>
			   <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">Current Profession:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $profession . '</font></td>
			  </tr>
			  <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma"># of years Experience:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $experience . '</font></td>
			  </tr>
			   <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">City:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $city . '</font></td>
			  </tr>
			   <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">E-mail Id:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $email . '</font></td>
			  </tr> 
			  <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">Phone Number:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $phone . '</font></td>
			  </tr>
			  <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">Mobile Number:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $mobile . '</font></td>
			  </tr>
			  <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">Prefered Date:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $date . '</font></td>
			  </tr> 
			  <tr>
				<td width="33%"><font color="#f35D14" size="2" face="Tahoma">Preferred Time:</font></td>
				<td width="67%" height="22"><font color="#333333" size="-1" face="Tahoma">' . $time . '</font></td>
			  </tr>
				</table></td>
		  </tr></table>';
        $messageUser = 'Dear ' . $name . ' ,<br/><br/>
				Thank you for visiting our website and for appointment with us. ';
        $subject = "Contact Information From Site.";
        $frm = $name;
        $mail = "abin@cambridgeeducation.net";
        $frmail = $email;
        $toemail = "abin@cambridgeeducation.net";
        $headers = "From: " . $frm . " <" . $frmail . ">\nContent-Type: text/html; charset=iso-8859-1";
        $header1 = "From: Cambridge Education <" . $mail . ">\nContent-Type: text/html; charset=iso-8859-1";
        if (@mail($toemail, $subject, "<font size=2 color=#333333 face=Arial, Helvetica, sans-serif>A New Appointment Form has been Received.</font><br/><br/>" . $message, $headers)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Thank you for visiting our website and for appointment with us. ");
            die();
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Please Try Later for Appointment with us");
            die();
        }
    }
}
?>
<h2>Career Development Counselling </h2>
<div class="innerWrapper">
    <h6>Fasten your Career Track by meeting with CAMBRIDGE Career Consultants!! </h6>
    <span class="gap"></span>
    <h3>Interested for Career Development </h3>
    <span class="gap"></span>
    <p>Please fill up this Form for Appointment:</p>
    <span class="gap"></span>
    <div class="formWRapper">
        <form name="contactform" class="form" method= "POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return validateForm(this);">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="formTable">
                <?php if (isset($_GET['msg'])) { ?>
                    <tr>
                        <td align="center"  style="color:green;"  colspan="2" valign="top"><?php echo $_GET['msg']; ?></td>
                    </tr>
                <?php } ?>	
                <tr>
                    <td colspan="2" height="5"></td>
                </tr>
                <tr>
                    <td width="30%"><label>Name<span class="red">*</span></label></td>
                    <td><input name="name_app" type="text" value="" lang="MUST" title="Name" class="inputField" /></td>
                </tr>
                <tr>
                    <td><label>Current Profession<span class="red">*</span> </label></td>
                    <td><input name="profession_app" type="text" lang="MUST" title="Current Profession" class="inputField" /></td>
                </tr>
                <tr>
                    <td><label># of years Experience<span class="red">*</span> </label></td>
                    <td>
                        <select class="inputSelect" name="experience_app" lang="MUST" title="Experience" >
                            <option disabled="disabled" selected="selected" value="">Select One</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                            <option value="19">19</option>
                            <option value="20">20</option>
                            <option value="21">21</option>
                            <option value="22">22</option>
                            <option value="23">23</option>
                            <option value="24">24</option>
                            <option value="25">25</option>
                            <option value="26">26</option>
                            <option value="27">27</option>
                            <option value="28">28</option>
                            <option value="29">29</option>
                            <option value="30">30</option>
                            <option value="31">31</option>
                            <option value="32">32</option>
                            <option value="33">33</option>
                            <option value="34">34</option>
                            <option value="35">35</option>
                            <option value="36">36</option>
                            <option value="37">37</option>
                            <option value="38">38</option>
                            <option value="39">39</option>
                            <option value="40">40</option>
                        </select>                             </td>
                </tr>
                <tr>
                    <td><label>City<span class="red">*</span></label></td>
                    <td>
                        <select class="inputSelect" name="city_app" lang="MUST" title="City" >
                            <option disabled="disabled" selected="selected" value="">Select one</option>
                            <option value="Abudhabi">Abu Dhabi</option>
                            <option value="Dubai">Dubai</option>
                            <option value="Alain">Al-Ain</option>
                            <option value="Sharjah">Sharjah</option>
                            <option value="Doha">Doha</option>
                            <option value="Others">Others</option>
                        </select></td>
                </tr>
                <tr>
                    <td><label>E-Mail ID<span class="red">*</span> </label></td>
                    <td><input name="email_app" type="text" lang="MUST(EMAIL)" title="E-mail ID" class="inputField" /></td>
                </tr>
                <tr>
                    <td><label>Phone Number</label> </td>
                    <td><input name="phone_app" type="text" class="inputField" /></td>
                </tr>
                <tr>
                    <td><label>Mobile Number<span class="red">*</span> </label></td>
                    <td><input name="mobile_app" type="text" lang="MUST(INT)" title="Mobile Number" class="inputField" /></td>
                </tr>
                <tr>
                    <td><label>Preferred Date<span class="red">*</span> </label> </td>
                    <td>
                        <input type="text" name="date_app" id="date_app" value="<?php echo $formvals['date_app']; ?>" lang="must" title="Preferred Date" class="inputField" readonly="readonly">&nbsp;<img src="<?php echo BASE_URL . DIR_REL ?>/themes/cambridge-education/images/clander_img.gif" width="20" height="20" id="reg_date_trigger108" title="Preferred Date" alt="Calender"/>
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: "date_app", // id of the input field
                                ifFormat: "%Y-%m-%d", // format of the input field
                                button: "reg_date_trigger108", // trigger for the calendar (button ID)
                                align: "TL",
                                showsTime: true, // alignment (defaults to "Bl")
                                singleClick: true
                            });
                        </script>	
                    </td>
                </tr>
                <tr>
                    <td><label>Preferred Time<span class="red">*</span> </label> </td>
                    <td><input name="time_app" type="text" lang="MUST" title="Preferred Time" class="inputField" /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="34%"><input type="checkbox" name="checkbox" id="checkbox" lang="MUST" title="Confirm" />
                                    Confirm<span class="red">*</span></td>
                                <td width="66%">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input name="contact_frm" type="submit" value="Submit" />
                        <input name="reset" type="reset" value="Reset" />
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </form>
    </div>
</div>
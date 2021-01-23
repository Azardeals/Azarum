<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
    <tbody><tr>
            <td align="center" style="padding:20px 0px 20px;font-family:Arial,Helvetica,sans-serif;font-size:18px;color:#cf1e36;font-weight:bold;text-align:center" colspan="2">Some other Deals</td>
        </tr>
        {{REPEAT_START}}
    <td valign="top" align="center" style="padding:0 10px 20px 20px;width:50%;">
        <table cellspacing="0" cellpadding="0" border="0" align="left" style="width:100%">
            <tbody>
                <tr>
                    <td style="border:1px solid #e5e5e5">
                        <table width="100%" cellspacing="0" cellpadding="0" align="center" style="background:#fff">
                            <tbody>
                                <tr>
                                    <td style="font:bold 16px Arial,Helvetica,sans-serif;padding:10px 10px 0">{{DEAL_NAME}}</td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:10px;" colspan="2">
                                        <a target="_blank" href="#"><img width="100%" height="100%" border="0" style="border:0" src="{{DEAL_IMAGE}}">
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 10px 6px;border-bottom:solid 1px #eae9e9">
                                        <table width="100%" cellspacing="0" cellpadding="0" align="center" style="background:#fff">
                                            <tbody>
                                                <tr>
                                                    <td width="160" valign="middle">
                                                        <span style="text-decoration:line-through;margin-right:5px;font:bold 15px Arial,Helvetica,sans-serif">
                                                            {{DEAL_ORIGNAL_PRICE}}</span>
                                                        <span style="color:#027011;font:bold 15px Arial,Helvetica,sans-serif">{{DEAL_OFFER_PRICE}}</span>
                                                    </td>
                                                    <td bgcolor="#ffffff" style="font-size:12px;line-height:16px;padding:5px 7px;border-radius:2px;border:2px solid #cf1e36;background-color:#cf1e36;width:30px;text-align:center;"><a target="_blank" style="text-decoration:none;color:#fff;font-weight:bold;" href="{{DEAL_URL}}">View Deal</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td valign="middle" height="8" colspan="3"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
    {{REPEAT_END}}
    <tr>
        <td style="text-align:center;padding-bottom:20px;" colspan="2"><a target="_blank" style="background:#cf1e36;display:inline-block;color:#ffffff;text-decoration:none;font:normal 20px Arial,Helvetica,sans-serif;text-align:center;padding:10px;padding-bottom:15px;border-radius:3px;width:200px;" href="{{DEAL_URL}}">View More</a>
        </td>
    </tr></tbody></table>
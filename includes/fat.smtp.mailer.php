<?php

function sendSmtpMail($to, $subject, $body, $smtp_details, &$error = '', $attachments = [])
{
    require_once dirname(__FILE__) . '/phpmailer/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPSecure = ($smtp_details['use_ssl'] == 1) ? 'ssl' : 'tls';
    $mail->Host = $smtp_details['host'];
    $mail->Port = $smtp_details['port'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_details['username'];
    $mail->Password = $smtp_details['password'];
    $mail->IsHTML(true);
    $mail->SMTPDebug = 0;
    $mail->AddAddress($to);
    $mail->From = $smtp_details['from'];
    $mail->FromName = $smtp_details['sender'];
    $mail->Subject = $subject;
    $mail->Body = $body;
    if (!empty($attachments)) {
        $mail->AddAttachment($attachments['file'], $attachments['filename']);
    }
    if (!$mail->Send()) {
        $message = "Hi,<br/><br/>";
        $message .= "Current status to send email for " . $subject . " to " . $to . " is NOT SENT via SMTP settings";
        $message .= ", due to: " . $mail->ErrorInfo;
        $message .= ".<br/><br/>";
        $message .= "Thanks,<br/><br/>";
        $message .= CONF_SITE_NAME . " Team";
        $sub = 'Mail could not sent via SMTP on ' . CONF_SERVER_NAME;
        mail(CONF_ADMIN_EMAIL_ID, $sub, $message); /* send a notification for email failure */
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . $smtp_details['sender'] . '<' . $smtp_details['from'] . "> \r\n";
        mail($to, $subject, $body, $headers); /* hit simple mail function for user */
        $error = $mail->ErrorInfo;
        return false;
    }
    return true;
}

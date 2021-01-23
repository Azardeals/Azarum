<?php

function sendSmtpMail($to, $subject, $body, $smtp_details, &$error = '', $attachments = [])
{
    $newline = "\n";
    $boundary = '----=_Mail_Next_Part_' . md5(time());
    $header = 'MIME-Version: 1.0' . $newline;
    $header .= 'To: ' . $to . $newline;
    $header .= 'Subject: ' . '=?UTF-8?B?' . base64_encode($subject) . '?=' . $newline;
    $header .= 'Date: ' . date('D, d M Y H:i:s O') . $newline;
    $header .= 'From: =?UTF-8?B?' . base64_encode($smtp_details['sender']) . '?=' . ' <' . $smtp_details['from'] . '>' . $newline;
    $header .= 'Return-Path: ' . $smtp_details['from'] . $newline;
    $header .= 'X-Mailer: PHP/' . phpversion() . $newline;
    $header .= 'Content-Type: multipart/related; boundary="' . $boundary . '"' . $newline . $newline;
    $message = '--' . $boundary . $newline;
    $message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $newline . $newline;
    $message .= '--' . $boundary . '_alt' . $newline;
    $message .= 'Content-Type: text/html; charset="utf-8"' . $newline;
    $message .= 'Content-Transfer-Encoding: 8bit' . $newline . $newline;
    $message .= $body . $newline;
    $message .= '--' . $boundary . '_alt--' . $newline;
    $message .= '--' . $boundary . '--' . $newline;
    $smtp_timeout = 5;
    if ($smtp_details['use_ssl']) {
        $host = 'ssl://' . $smtp_details['host'];
    } else {
        $host = $smtp_details['host'];
    }
    $handle = fsockopen($host, $smtp_details['port'], $errno, $errstr, $smtp_timeout);
    if (!$handle) {
        $error = 'Error: ' . $errstr . ' (' . $errno . ')';
        return false;
    } else {
        if (substr(PHP_OS, 0, 3) != 'WIN') {
            socket_set_timeout($handle, $smtp_timeout, 0);
        }
        while ($line = fgets($handle, 515)) {
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . "\r\n");
        $reply = '';
        while ($line = fgets($handle, 515)) {
            $reply .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        if (substr($reply, 0, 3) != 250) {
            $error = 'Error: EHLO not accepted from server!';
            return false;
        }
        if (!empty($smtp_details['username']) && !empty($smtp_details['password'])) {
            fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . "\r\n");
            $reply = '';
            while ($line = fgets($handle, 515)) {
                $reply .= $line;
                if (substr($line, 3, 1) == ' ') {
                    break;
                }
            }
            if (substr($reply, 0, 3) != 250) {
                $error = 'Error: EHLO not accepted from server!';
                return false;
            }
            fputs($handle, 'AUTH LOGIN' . "\r\n");
            $reply = '';
            while ($line = fgets($handle, 515)) {
                $reply .= $line;
                if (substr($line, 3, 1) == ' ') {
                    break;
                }
            }
            if (substr($reply, 0, 3) != 334) {
                $error = 'Error: AUTH LOGIN not accepted from server!';
                return false;
            }
            fputs($handle, base64_encode($smtp_details['username']) . "\r\n");
            $reply = '';
            while ($line = fgets($handle, 515)) {
                $reply .= $line;
                if (substr($line, 3, 1) == ' ') {
                    break;
                }
            }
            if (substr($reply, 0, 3) != 334) {
                $error = 'Error: Username not accepted from server!';
                return false;
            }
            fputs($handle, base64_encode($smtp_details['password']) . "\r\n");
            $reply = '';
            while ($line = fgets($handle, 515)) {
                $reply .= $line;
                if (substr($line, 3, 1) == ' ') {
                    break;
                }
            }
            if (substr($reply, 0, 3) != 235) {
                $error = 'Error: Password not accepted from server!';
                return false;
            }
        } else {
            $error = 'Error: Invalid username or password!';
            return false;
        }
        fputs($handle, 'MAIL FROM: <' . $smtp_details['from'] . '>' . "\r\n");
        $reply = '';
        while ($line = fgets($handle, 515)) {
            $reply .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        if (substr($reply, 0, 3) != 250) {
            $error = 'Error: MAIL FROM not accepted from server!';
            return false;
        }
        fputs($handle, 'RCPT TO: <' . $to . '>' . "\r\n");
        $reply = '';
        while ($line = fgets($handle, 515)) {
            $reply .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
            $error = 'Error: RCPT TO not accepted from server!';
            return false;
        }
        fputs($handle, 'DATA' . "\r\n");
        $reply = '';
        while ($line = fgets($handle, 515)) {
            $reply .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        if (substr($reply, 0, 3) != 354) {
            $error = 'Error: DATA not accepted from server!';
            return false;
        }
        // According to rfc 821 we should not send more than 1000 including the CRLF
        $message = str_replace("\r\n", "\n", $header . $message);
        $message = str_replace("\r", "\n", $message);
        $lines = explode("\n", $message);
        foreach ($lines as $line) {
            $results = str_split($line, 998);
            foreach ($results as $result) {
                if (substr(PHP_OS, 0, 3) != 'WIN') {
                    fputs($handle, $result . "\r\n");
                } else {
                    fputs($handle, str_replace("\n", "\r\n", $result) . "\r\n");
                }
            }
        }
        fputs($handle, '.' . "\r\n");
        $reply = '';
        while ($line = fgets($handle, 515)) {
            $reply .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        if (substr($reply, 0, 3) != 250) {
            $error = 'Error: DATA not accepted from server!';
            return false;
        }
        fputs($handle, 'QUIT' . "\r\n");
        $reply = '';
        while ($line = fgets($handle, 515)) {
            $reply .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        if (substr($reply, 0, 3) != 221) {
            $error = 'Error: QUIT not accepted from server!';
            return false;
        }
        fclose($handle);
        return true;
    }
    return false;
}

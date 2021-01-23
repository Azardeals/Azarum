<?php

require_once '../application-top.php';
$post = getPostedData();

function site_url()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

switch (strtoupper($post['mode'])) {
    case 'FB_AUTHENTICATION':
        require_once '../facebook-php-sdk/autoload.php';
        $fb = new Facebook\FacebookApp($post['app_id'], $post['secretkey']);
        try {
            $accessToken = $fb->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo $e->getMessage();
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $error_msg = t_lang('M_TXT_FACEBOOK_API_KEY_AND_SECRET_KEY_IS_NOT_VALID');
        $graph_url = "https://graph.facebook.com/app?access_token=" . $accessToken;
        $response = file_get_contents($graph_url);
        $decoded_response = json_decode($response);
        if (!empty($decoded_response)) {
            $fbsiteUrl = $decoded_response->link;
            $siteUrl = site_url();
            if (strpos($fbsiteUrl, $siteUrl) !== false) {
                $arr = ['status' => 1, 'msg' => 'valid'];
                die(convertToJson($arr));
            } else {
                $arr = ['status' => 0, 'msg' => $error_msg];
                die(convertToJson($arr));
            }
        } else {
            $arr = ['status' => 0, 'msg' => $error_msg];
            die(convertToJson($arr));
        }
        break;
    case 'VERIFY_MERCHANT_AUTHRIZED':
        /*
          Sandbox URL: https://apitest.authorize.net/xml/v1/request.api
          Production URL: https://api.authorize.net/xml/v1/request.api
         */
        $url = "https://api.authorize.net/xml/v1/request.api";
        $xml = '<authenticateTestRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
				<merchantAuthentication>
					<name>' . $post["login_app_id"] . '</name>
					<transactionKey>' . $post["transactionkey"] . '</transactionKey>
				</merchantAuthentication>
				</authenticateTestRequest>';
        $headers = [
            "Content-type: text/xml",
            "Content-length: " . strlen($xml),
            "Connection: close",
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            $arr = ['status' => 0, 'msg' => curl_error($ch)];
            die(convertToJson($arr));
        } else {
            $xml = simplexml_load_string($data);
            $text = $xml->messages->message->text;
            $pos = strpos($text, 'Successful');
            if ($pos !== false) {
                $arr = ['status' => 1, 'msg' => 'valid'];
                die(convertToJson($arr));
            } else {
                $text1 = "Login ID and Transaction key are not valid";
                $arr = ['status' => 0, 'msg' => $text1];
                die(convertToJson($arr));
            }
            curl_close($ch);
        }
        break;
}

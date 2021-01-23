<?php

/**
 * The AuthorizeNet PHP SDK. Include this file in your project.
 *
 * @package AuthorizeNet
 */
require dirname(__FILE__) . '/lib/AuthorizeNet/autoload.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeAimPayController
{

    const SANDBOX = "https://apitest.authorize.net";
    const PRODUCTION = "https://api2.authorize.net";
    const VERSION = "2.0.0";

    public function send($authAry, $post)
    {
        /* Create a merchantAuthenticationType object with authentication details
          retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($authAry['login_id']);
        $merchantAuthentication->setTransactionKey($authAry['transaction_key']);
        // Set the transaction's refId
        $refId = 'ref' . time();
        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber(str_replace(' ', '', $post['card_num']));
        $creditCard->setExpirationDate($post['expire_year'] . "-" . $post['expire_month']);
        $creditCard->setCardCode($post['card_code']);
        // Add the payment data to a paymentType object
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);
        // Create order information
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber(rand());
        $orderPaymentGatewayDescription = 'Deal Products';
        $order->setDescription($orderPaymentGatewayDescription);
        // Set the customer's Bill To address
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName(trim($post['card_name']));
        $customerAddress->setLastName(trim($post['last_name']));
        $customerAddress->setCompany(trim($post['card_name']));
        $customerAddress->setAddress($post['billing_address']);
        $customerAddress->setCity($post['city']);
        $customerAddress->setState($post['state']);
        $customerAddress->setZip($post['postal_code']);
        $customerAddress->setCountry($post['country']);
        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($post['user_id']);
        $customerData->setEmail($post['email']);
        // Add values for transaction settings
        $duplicateWindowSetting = new AnetAPI\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");
        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($post['amount']);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $actionUrl = (CONF_PAYMENT_PRODUCTION == 1) ? static::PRODUCTION : static::SANDBOX;
        $response = $controller->executeWithApiResponse($actionUrl);
        return $response;
    }

}

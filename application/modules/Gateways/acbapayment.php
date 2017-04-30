<?php


function acbapayment_config(){
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"Acba Payment"),
	 "merchant_id" => array("FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "20", ),
     "merchant_key" => array("FriendlyName" => "Merchant Key", "Type" => "text", "Size" => "16", ),
     "environment" => array("FriendlyName" => "Environment", "Type" => "dropdown", "Options" =>"TEST,LIVE", "Description" => "TEST or LIVE", ),
	 "website" => array("FriendlyName" => "Website name", "Type" => "text", "Size" => "20", ),
	 "industry_type" => array("FriendlyName" => "Industry Name", "Type" => "text", "Size" => "20", ),
	);
	return $configarray;
}

function acbapayment_link($params) {

    $payment_language = "en";
    $payment_url = 'https://ipaytest.arca.am:8445/payment/merchants/'.$params['merchant_id'].'/payment_'.$payment_language.'.html?';
    $currency_codes = array(
        'AMD' => 051,
        'RUB' => 643,
        'USD' => 840,
        'EUR' => 978,
    );



    // echo $params['invoiceData']->checkoutTotal;
//die;
    /*echo "<pre>";
 var_dump($params);*/


    // Check order status
    $post_data = array(
        'userName' =>$params['merchant_id'],
        'password' =>$params['merchant_key'],
        'orderNumber' =>$params['invoiceid'],
        'language' =>$payment_language,
    );
    // Get order status
    $result = connect(2, $post_data);
    /*
        0 Обработка запроса прошла без системных ошибок
        2 Заказ отклонен по причине ошибки в реквизитах платежа
        5 Ошибка значения параметра запроса
        6 Незарегистрированный OrderId
     */

    if($result->errorCode == 0 && ($result->orderStatus == 6 || $result->orderStatus == 3)){ //
        $post_data = array(
            'userName' =>$params['merchant_id'],
            'password' =>$params['merchant_key'],
            'orderId' =>$result->attributes[0]->value,
        );
        var_dump($result);
        die;
        $result = connect(3,$post_data);
        if( $result->errorCode == 0){

        }
        //var_dump($result);

        $payment_url .= $result->attributes[0]->name .'='.$result->attributes[0]->value;
    }elseif($result->errorCode == 6) {

        //echo $params['amount'];
        $post_data = array(
            'userName' =>$params['merchant_id'],
            'password' =>$params['merchant_key'],
            'orderNumber' =>$params['invoiceid'],
            'amount' => $params['invoiceData']->checkoutTotal*100,
            'currency' => $currency_codes[$params["currency"]],
            'returnUrl' => 'https://testmeto.com',
            'description' => 'Payment for order: '.$params['invoiceid'],
            'language' => 'en',
            'clientId' => $params['bookinUser'],
        );
        $result = connect( 1, $post_data );

    }
/*
    var_dump($result);
    die;*/
    // else create new



    if($result->errorCode == 0){
        if($result->formUrl){
            $payment_url = $result->formUrl;
        }
        $code = '<a class="btn btn-default" href="'.$payment_url.'">Pay with Acba Online</a>';
        return $code;
        //header('Location: '.$result->formUrl);
    } else {
        //print_r($result);
        return '<span>' . $result->errorCode . ' : ' . $result->errorMessage . '</span>';

    }

}

/********************************************************************
 * Con types
 * 1 - register
 * 2 - get order status
 * 3 - reverse
 ********************************************************************/
function connect($con_type, $post_data){
    $con_array = array(
      '1' => 'https://ipaytest.arca.am:8445/payment/rest/register.do',
      '2' =>  'https://ipaytest.arca.am:8445/payment/rest/getOrderStatusExtended.do',
      '3' =>  'https://ipaytest.arca.am:8445/payment/rest/reverse.do'
    );

    $curl = curl_init($con_array[$con_type]);

    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER , true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    $x = curl_exec($curl);

    curl_close($curl);
    $result = json_decode($x);
    return $result;
}
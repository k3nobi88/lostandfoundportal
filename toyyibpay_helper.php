<?php
/**
 * toyyibpay_helper.php
 * Creates a ToyyibPay bill and returns the redirect URL.
 */
function toyyibpayCreateBill(
    $billName,
    $billDescription,
    $amount,
    $reference,
    $returnUrl,
    $callbackUrl,
    $payerName  = 'Customer',
    $payerEmail = 'customer@lostandfound.com',
    $payerPhone = '0123456789'
) {
    // ToyyibPay amount is in sen (cents). RM5.49 = 549
    $amountInSen = (int)round($amount * 100);

    // Strip special characters from bill name and description (ToyyibPay requirement)
    $billName        = preg_replace('/[^a-zA-Z0-9 _]/', '', $billName);
    $billDescription = preg_replace('/[^a-zA-Z0-9 _]/', '', $billDescription);

    $postData = [
        'userSecretKey'           => TOYYIBPAY_SECRET_KEY,
        'categoryCode'            => TOYYIBPAY_CATEGORY_CODE,
        'billName'                => substr($billName, 0, 30),
        'billDescription'         => substr($billDescription, 0, 100),
        'billPriceSetting'        => 1,        // fixed amount
        'billPayorInfo'           => 1,        // collect payer info
        'billAmount'              => $amountInSen,
        'billReturnUrl'           => $returnUrl,
        'billCallbackUrl'         => $callbackUrl,
        'billExternalReferenceNo' => $reference,
        'billTo'                  => $payerName,
        'billEmail'               => $payerEmail,
        'billPhone'               => $payerPhone,
        'billSplitPayment'        => 0,
        'billSplitPaymentArgs'    => '',
        'billPaymentChannel'      => 0,        // 0 = FPX only (sandbox supports this)
        'billContentEmail'        => 'Thank you for your payment on Lost and Found Portal.',
        'billChargeToCustomer'    => 1,        // fees charged to customer
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST,           1);
    curl_setopt($curl, CURLOPT_URL,            TOYYIBPAY_BASE_URL . '/index.php/api/createBill');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,     $postData);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // OK for localhost dev
    curl_setopt($curl, CURLOPT_TIMEOUT,        30);

    $response  = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        return ['success' => false, 'error' => 'Connection error: ' . $curlError];
    }

    $result = json_decode($response, true);

    if (!$result || empty($result[0]['BillCode'])) {
        return ['success' => false, 'error' => 'ToyyibPay error: ' . $response];
    }

    return [
        'success'     => true,
        'bill_code'   => $result[0]['BillCode'],
        'payment_url' => TOYYIBPAY_BASE_URL . '/' . $result[0]['BillCode'],
    ];
}
<?php

// This class was generated on Mon, 16 Apr 2018 19:30:07 UTC by version 0.1.0-dev+560365 of Braintree SDK Generator
// PaymentExecuteRequest.php
// @version 0.1.0-dev+560365
// @type request
// @data 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
// DO NOT EDIT

namespace PayPal\v1\Payments;

use BraintreeHttp\HttpRequest;

class PaymentExecuteRequest extends HttpRequest
{
    function __construct($paymentId)
    {
        parent::__construct("/v1/payments/payment/{payment_id}/execute?", "POST");
        
        $this->path = str_replace("{payment_id}", urlencode($paymentId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

    
    public function paypalPartnerAttributionId($paypalPartnerAttributionId)
    {
        $this->headers["PayPal-Partner-Attribution-Id"] = $paypalPartnerAttributionId;
    }
    public function paypalRequestId($paypalRequestId)
    {
        $this->headers["PayPal-Request-Id"] = $paypalRequestId;
    }
}

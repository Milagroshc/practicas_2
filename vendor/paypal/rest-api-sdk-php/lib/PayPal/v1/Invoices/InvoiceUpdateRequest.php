<?php

// This class was generated on Tue, 30 Jan 2018 16:20:28 CST by version 0.1.0-dev+dcaee0-dirty of Braintree SDK Generator
// InvoiceUpdateRequest.php
// @version 0.1.0-dev+dcaee0-dirty
// @type request
// @data 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
// DO NOT EDIT

namespace PayPal\v1\Invoices;

use BraintreeHttp\HttpRequest;class InvoiceUpdateRequest extends HttpRequest 
{
    function __construct($invoiceId)
    {
        parent::__construct("/v1/invoicing/invoices/{invoice_id}?", "PUT");
        
        $this->path = str_replace("{invoice_id}", urlencode($invoiceId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

    
    public function notifyMerchant($notifyMerchant)
    {
        $param = $notifyMerchant;
        $this->path .= "notify_merchant=". urlencode($param) . "&";
    }
}

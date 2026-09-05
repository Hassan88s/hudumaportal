<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'event-paypal-ipn',
        'event-paytm-ipn',
          '/webhook/flutterwave',

        'admin-home/update-static-option',
        'admin-home/get-static-option',
        'admin-home/set-static-option',
        'contribution-paytm-ipn',


        'subscription/paytm/ipn',
        'subscription/payfast-ipn',
        'subscription/cinetpay-ipn',
        'subscription/zitopay-ipn',
        'subscription/kineticpay-ipn',
        'subscription/paytabs-ipn',


        'buyer/paytm-ipn',
        'buyer/cashfree-ipn',
        'buyer/payfast-ipn',
        'buyer/cinetpay-ipn',
        'buyer/zitopay-ipn',
        'buyer/kineticpay-ipn',
        'buyer/paytabs-ipn',


        'cashfree/ipn',
        'cinetpay-ipn',
        'paypal/ipn',
        'paytm/ipn',
        'payfast-ipn',
        'zitopay-ipn',
        'kineticpay-ipn',
        'paytabs-ipn',

        'jobpost/cashfree-ipn',

        // ClickPesa (browser callback + server-to-server webhook)
        'wallet/clickpesa-ipn',
        'seller/wallet/clickpesa-ipn',
        'clickpesa/webhook',
        'clickpesa/return',
    ];
}

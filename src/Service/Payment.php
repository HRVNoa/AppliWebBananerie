<?php

namespace App\Service;

class Payment
{
    public function processPayment($quantity){

        $key = $_ENV['STRIPESECRETKEY'];
        \Stripe\Stripe::setApiKey($key);
        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'http://reservationbananerie/';

        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => [[
                # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                'price' => 'price_1OlSBnJ9RLT9aPhHsfAqbnR1',
                'quantity' => $quantity,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/bourse/acheter/success',
            'cancel_url' => $YOUR_DOMAIN . '/bourse/acheter/fail',
            'automatic_tax' => [
                'enabled' => true,
            ],
        ]);

        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
    }
}
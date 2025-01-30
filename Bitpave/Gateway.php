<?php

namespace App\Gateways\Bitpave;

use LaraPay\Framework\Interfaces\GatewayFoundation;
use Illuminate\Support\Facades\Http;
use LaraPay\Framework\Payment;
use Illuminate\Http\Request;
use Exception;

class Gateway extends GatewayFoundation
{
    /**
     * Define the gateway identifier. This identifier should be unique. For example,
     * if the gateway name is "PayPal Express", the gateway identifier should be "paypal-express".
     *
     * @var string
     */
    protected string $identifier = 'bitpave';

    /**
     * Define the gateway version.
     *
     * @var string
     */
    protected string $version = '1.0.0';

    protected $currencies = ['USD'];

    public function config(): array
    {
        return [
            'client' => [
                'label' => 'BitPave Client ID',
                'description' => 'Enter your Bitpave client ID',
                'type' => 'text',
                'rules' => ['required'],
            ],
            'client_secret' => [
                'label' => 'BitPave Client Secret',
                'description' => 'Enter your Bitpave client secret',
                'type' => 'text',
                'rules' => ['required'],
            ],
            'wallet' => [
                'label' => 'Bitcoin Wallet',
                'description' => 'Enter the bitcoin wallet address on which the payments will be received',
                'type' => 'text',
                'rules' => ['required'],
            ],
        ];
    }

    public function pay($payment)
    {
        $gateway = $payment->gateway;
        $checkout = Http::post('https://bitpave.com/api/checkout/create', [
            'client' => $gateway->config('client'),
            'client_secret' => $gateway->config('client_secret'),

            'name' => $payment->description,
            'wallet' => $gateway->config('wallet'),
            'price' => $payment->total(), // price in $ USD

            'callback_url' => $payment->webhookUrl(),
            'success_url' => $payment->successUrl(),
            'cancel_url' => $payment->cancelUrl(),
        ]);

        if ($checkout->failed()) {
            throw new Exception('Failed to create checkout');
        }

        return redirect()->away($checkout['checkout_url']);
    }

    public function callback(Request $request)
    {
        // check if signature is set and matches the client secret
        if($request->get('signature') !== $this->config('client_secret')) {
            throw new Exception('Invalid signature');
        }

        // check if the payment is successful
        if($request->get('status') !== 'completed') {
            throw new Exception('Payment status is not completed');
        }

        $payment = Payment::find($request->get('payment_id'));

        if(!$payment) {
            throw new Exception('Payment not found');
        }

        $payment->completed();
    }
}

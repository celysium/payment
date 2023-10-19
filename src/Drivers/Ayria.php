<?php

namespace Celysium\Payment\Drivers;

use Celysium\Payment\Contracts\DriverInterface;
use Celysium\Payment\Exceptions\PurchaseFailedException;
use Celysium\Payment\GatewayForm;
use Celysium\Payment\Payment;
use Celysium\Payment\Receipt;
use Illuminate\Support\Facades\Http;

class Ayria implements DriverInterface
{
    /**
     * Ayria constructor.
     *
     * @param Payment $payment
     */
    public function __construct(protected Payment $payment)
    {
    }

    /**
     * Purchase Invoice.
     *
     * @return self
     *
     * @throws PurchaseFailedException
     */
    public function purchase(callable $callback): DriverInterface
    {
        $data = [
            "referralCode"            => $this->payment->config->referralCode,
            "amount"                  => $this->payment->amount,
            "payerMobile"             => $this->payment->detail('mobile'),
            "payerName"               => $this->payment->detail('name'),
            "description"             => $this->payment->config->description,
            "paymentNumber"           => $this->payment->id,
            "extraData"               => "",
            "kalas"                   => [],
            "issuerMustVerifyPayment" => true,
            "callbackUrl"             => $this->payment->config->callbackUrl
        ];

        $response = Http::asJson()
            ->withHeaders([
                "APG-WALLET-ID" => $this->payment->config->walletId,
                "APG-API-KEY"   => $this->payment->config->apiKey,
            ])
            ->post($this->payment->config->apiPurchaseUrl, $data);

        if($response->failed()) {
            throw new PurchaseFailedException($response->body());
        }

        $data = $response->json();
        $this->payment->detail($data);

        $this->payment->transactionId($data['referenceCode']);

        $callback($this->payment);

        return $this;
    }

    /**
     * Pay the Invoice
     *
     * @return GatewayForm
     */
    public function pay(): GatewayForm
    {
        $payUrl = $this->payment->getDetail('paymentUrl');

        return new GatewayForm($payUrl, [], 'GET');
    }

    /**
     * Verify payment
     *
     *
     * @param array $request
     * @return Receipt
     * @throws PurchaseFailedException
     */
    public function verify(array $request): Receipt
    {
        $response = Http::asJson()
            ->withHeaders([
                "APG-WALLET-ID" => $this->payment->config->walletId,
                "APG-API-KEY"   => $this->payment->config->apiKey,
            ])
            ->post($this->payment->config->apiVerificationUrl . $request['referenceCode']);

        if($response->failed()) {
            throw new PurchaseFailedException($response->body());
        }

        $data = $response->json();

        $receipt = new Receipt($data['trackingNumber']);
        $receipt->detail($data);

        return $receipt;
    }

    public function refund(): Receipt
    {
        return new Receipt('');
    }

    public function callbackId(array $request)
    {
        return $request['paymentNumber'];
    }
}

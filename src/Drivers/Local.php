<?php

namespace Celysium\Payment\Drivers;

use Celysium\Payment\Contracts\DriverInterface;
use Celysium\Payment\Exceptions\InvalidPaymentException;
use Celysium\Payment\Exceptions\PurchaseFailedException;
use Celysium\Payment\GatewayForm;
use Celysium\Payment\Payment;
use Celysium\Payment\Receipt;
use Illuminate\Support\Facades\Cache;

class Local implements DriverInterface
{
    /**
     * Behpardakht constructor.
     *
     * @param Payment $payment
     */
    public function __construct(protected Payment $payment)
    {
    }

    /**
     * Purchase Invoice.
     *
     * @param callable $callback
     * @return DriverInterface
     */
    public function purchase(callable $callback): DriverInterface
    {
        $transactionId = time();

        $data = [
            'id'            => $this->payment->id,
            'quick'         => $this->payment->quick ?? false,
            'amount'        => $this->payment->amount,
            'callbackUrl'   => $this->payment->config->callbackUrl,
            'status'        => 0,
            'transactionId' => $transactionId
        ];

        Cache::putMany($data, now()->addMinutes(10));

        $this->payment->transactionId($transactionId);

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
        $payUrl = $this->payment->config->apiPaymentUrl;

        $data = [
            'transactionId' => $this->payment->transactionId,
        ];

        return new GatewayForm($payUrl, $data, 'GET');
    }

    /**
     * Verify payment
     *
     *
     * @throws InvalidPaymentException
     */
    public function verify(array $request): Receipt
    {
        $status = (int)cache('status');

        if ($status !== 1) {
            $this->notVerified();
        }

        return new Receipt(time());
    }

    /**
     * Trigger an exception
     *
     * @throws PurchaseFailedException
     */
    protected function purchaseFailed()
    {
        throw new PurchaseFailedException('پرداخت موفقیت آمیز نبود.');
    }

    /**
     * Trigger an exception
     *
     * @throws InvalidPaymentException
     */
    private function notVerified()
    {
        throw new InvalidPaymentException('پراخت موفقیت آمیز نبود');
    }

    public function refund(): Receipt
    {
        return new Receipt(time());
    }

    public function callbackId(array $request)
    {
        return Cache::get('transactionId');
    }
}

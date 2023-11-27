<?php

namespace Celysium\Payment\Drivers;

use Celysium\Payment\Contracts\DriverInterface;
use Celysium\Payment\Exceptions\InvalidPaymentException;
use Celysium\Payment\Exceptions\PurchaseFailedException;
use Celysium\Payment\GatewayForm;
use Celysium\Payment\Payment;
use Celysium\Payment\Receipt;
use Illuminate\Support\Facades\Http;
use SoapClient;
use SoapFault;

class Saman implements DriverInterface
{
    /**
     * Saman constructor.
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
     * @return self
     * @throws PurchaseFailedException
     */
    public function purchase(callable $callback): DriverInterface
    {
        $data = [
            'action'      => 'token',
            'TerminalId'  => $this->payment->config->merchantId,
            'Amount'      => $this->payment->amount,
            'ResNum'      => $this->payment->id,
            'RedirectUrl' => $this->payment->config->callbackUrl,
            'CellNumber'  => '',
        ];

        if ($mobile = $this->payment->getDetail('mobile')) {
            $data['CellNumber'] = $mobile;
        }
        $response = Http::acceptJson()
            ->post($this->payment->config->apiPurchaseUrl, $data)
            ->json();

        if ($response['status'] < 0) {
            throw new PurchaseFailedException($response['errorDesc']);
        }

        $this->payment->transactionId($response['token']);

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
            'Token' => $this->payment->transactionId,
        ];
        return new GatewayForm($payUrl, $data, 'POST');
    }

    /**
     * Verify payment
     * @param array $request
     * @return Receipt
     * @throws InvalidPaymentException
     */
    public function verify(array $request): Receipt
    {
        $data = [
            'RefNum'         => $request['RefNum'],
            'TerminalNumber' => $this->payment->config->merchantId,
        ];

        $response = Http::acceptJson()
            ->post($this->payment->config->apiVerificationUrl, $data)
            ->json();

        if (!$response['Success']) {
            throw new InvalidPaymentException($response['ResultDescription']);
        }

        $receipt = new Receipt($response['TransactionDetail']['StraceNo']);
        $receipt->detail($response['TransactionDetail']);

        return $receipt;
    }

    /**
     * @throws SoapFault
     * @throws InvalidPaymentException
     */
    public function refund(): Receipt
    {
        $data = [
            'username'            => $this->payment->config->username,
            'password'            => $this->payment->config->password,
            'refNum'              => $this->payment->transactionId,
            'resNum'              => $this->payment->detail('saleOrderId'),
            'transactionTermId'   => $this->payment->config->transactionTermId,
            'refundTermId'        => $this->payment->config->refundTermId,
            'amount'              => $this->payment->amount,
            'requestId'           => $this->payment->id,
            'exeTime'             => 0,
            'email'               => $this->payment->getDetail('email', ''),
            'cellNumber'          => $this->payment->getDetail('mobile', ''),
            'documentDescription' => $this->payment->getDetail('description', '')
        ];

        $soap = new SoapClient($this->payment->config->apiRefundUrl);

        if ($data['amount'] < 150000000) {
            $response = $soap->Refund_Reg($data);
        } else {
            $response = $soap->Refund_Reg_breakable($data);
        }

        if ($response->ErrorCode != 0) {
            $this->notRefunded($response->ErrorCode);
        }

        $receipt = new Receipt($response->ReferenceId);

        $receipt->detail([
            'ActionName'    => $response->ActionName,
            'RequestStatus' => $response->RequestStatus,
            'Description'   => $response->Description,
            'ErrorMessage'  => $response->ErrorMessage,
            'ErrorCode'     => $response->ErrorCode,
            'ReferenceId'   => $response->ReferenceId,
        ]);

        return $receipt;
    }

    public function callbackId(array $request)
    {
        return $request['Token'];
    }

    /**
     * Trigger an exception
     *
     * @param $status
     * @throws InvalidPaymentException
     */
    private function notRefunded($status)
    {
        $message = match ($status) {
            -1 => 'خطای ناشناخته',
            1 => 'پارامتر ورودی سرویس صحیح نمی باشد',
            2 => 'نام کاربری با رمز عبور صحیح نمی باشد',
            3 => 'تراکنشی با مشخصات ارسال شده قبلا ثبت نشده است',
            4 => 'این درخواست قبلا ثبت شده است',
            5 => 'جمع مبالغ درخواست های استرداد از مبلغ اصلی تراکنش بیشتر است',
            6 => 'درخواست استرداد نهایی شده و امکان تغییر آن وجود ندارد',
            11 => 'در خواست دیگری با همین مشخصات در حال ثبت می باشد لطفا مجددا تلاش نمایید',
            13 => 'اجازه اجرای درخواست وجود ندارد لطفا با ادمین سیستم تماس حاصل فرمایید',
            default => 'خطای ناشناخته ای رخ داده است.',
        };
        throw new InvalidPaymentException($message);
    }
}

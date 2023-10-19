<?php

namespace Celysium\Payment\Drivers;

use Illuminate\Support\Arr;
use Celysium\Payment\Contracts\DriverInterface;
use Celysium\Payment\Exceptions\InvalidPaymentException;
use Celysium\Payment\Exceptions\PurchaseFailedException;
use Celysium\Payment\GatewayForm;
use Celysium\Payment\Receipt;
use SoapClient;
use SoapFault;
use Celysium\Payment\Payment;

class Behpardakht implements DriverInterface
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
     * payment.
     *
     * @param callable $callback
     * @return DriverInterface
     *
     * @throws PurchaseFailedException
     * @throws SoapFault
     */

    public function purchase(callable $callback): DriverInterface
    {
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == "HTTP/2.0") {
            $context = stream_context_create(
                [
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    )]
            );

            $soap = new SoapClient($this->payment->config->apiPurchaseUrl, [
                'stream_context' => $context
            ]);
        } else {
            $soap = new SoapClient($this->payment->config->apiPurchaseUrl);
        }
        $data = [
            'terminalId' => $this->payment->config->terminalId,
            'userName' => $this->payment->config->username,
            'userPassword' => $this->payment->config->password,
            'callBackUrl' => $this->payment->config->callbackUrl,
            'amount' => $this->payment->amount,
            'localDate' => now()->format('Ymd'),
            'localTime' => now()->format('Gis'),
            'orderId' => $this->payment->id,
            'additionalData' => $this->payment->getDetail('additionalData', $this->payment->config->descripton),
            'payerId' => $this->payment->getDetail('payerId', 0)
        ];

        $this->payment->detail(Arr::only($data, ['orderId', 'additionalData', 'payerId']));

        $response = $soap->bpPayRequest($data);

        if ($response->return == 21) {
            throw new PurchaseFailedException($this->translateStatus('21'), 21);
        }

        $data = explode(',', $response->return);

        if ($data[0] != "0") {
            throw new PurchaseFailedException($this->translateStatus($data[0]), (int)$data[0]);
        }

        $this->payment->transactionId($data[1]);

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
            'driver' => $this->payment->driver,
            'RefId' => $this->payment->transactionId,
        ];

        //set mobileNo for get user cards
        if ($mobile = $this->payment->getDetail('mobile')) {
            $data['MobileNo'] = $mobile;
        }

        return new GatewayForm($payUrl, $data, 'POST');
    }

    /**
     * Verify payment
     *
     * @param array $request
     * @return Receipt
     *
     * @throws InvalidPaymentException
     * @throws SoapFault
     */
    public function verify(array $request): Receipt
    {
        $resCode = $request['ResCode'];
        if ($resCode != '0') {
            throw new InvalidPaymentException($this->translateStatus($resCode), $resCode);
        }

        $data = [
            'terminalId' => $this->payment->config->terminalId,
            'userName' => $this->payment->config->username,
            'userPassword' => $this->payment->config->password,
            'orderId' => $this->payment->id,
            'saleOrderId' => $this->payment->detail('SaleOrderId'),
            'saleReferenceId' => $this->payment->detail('SaleReferenceId')
        ];

        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == "HTTP/2.0") {
            $context = stream_context_create(
                [
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    )]
            );

            $soap = new SoapClient($this->payment->config->apiPurchaseUrl, [
                'stream_context' => $context
            ]);
        } else {
            $soap = new SoapClient($this->payment->config->apiPurchaseUrl);
        }

        // step1: verify request
        $verifyResponse = (int)$soap->bpVerifyRequest($data)->return;
        if ($verifyResponse != 0) {
            // rollback money and throw exception
            // avoid rollback if request was already verified
            if ($verifyResponse != 43) {
                $soap->bpReversalRequest($data);
            }
            throw new InvalidPaymentException($this->translateStatus($verifyResponse), $verifyResponse);
        }

        // step2: settle request
        $settleResponse = $soap->bpSettleRequest($data)->return;
        if ($settleResponse != 0) {
            // rollback money and throw exception
            // avoid rollback if request was already settled/reversed
            if ($settleResponse != 45 && $settleResponse != 48) {
                $soap->bpReversalRequest($data);
            }
            throw new InvalidPaymentException($this->translateStatus($settleResponse), $settleResponse);
        }

        $receipt = new Receipt($data['saleReferenceId']);
        $receipt->detail([
            "RefId" => $request['RefId'],
            "SaleOrderId" => $request['SaleOrderId'],
            "CardHolderPan" => $request['CardHolderPan'],
            "CardHolderInfo" => $request['CardHolderInfo'],
            "SaleReferenceId" => $request['SaleReferenceId'],
        ]);

        return $receipt;
    }

    /**
     * @throws SoapFault
     * @throws InvalidPaymentException
     */
    public function refund(): Receipt
    {
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == "HTTP/2.0") {
            $context = stream_context_create(
                [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ]
            );

            $soap = new SoapClient($this->payment->config->apiRefundUrl, ['stream_context' => $context]);
        } else {
            $soap = new SoapClient($this->payment->config->apiRefundUrl);
        }

        $data = [
            'terminalId' => $this->payment->config->terminalId,
            'userName' => $this->payment->config->username,
            'userPassword' => $this->payment->config->password,
            'orderId' => $this->payment->id,
            'saleOrderId' => $this->payment->getDetail('saleOrderId'),
            'saleReferenceId' => $this->payment->getDetail('saleReferenceId'),
            'refundAmount' => $this->payment->amount,
        ];

        $response = $soap->bpRefundRequest($data);
        $data = explode(',', $response->return);

        if ($data[0] != "0") {
            throw new InvalidPaymentException($this->translateStatus($data[0]));
        }


        $receipt = new Receipt($data[1]);

        $receipt->detail([
            'status' => $data[0],
            'ReferenceId' => $data[1],
        ]);

        return $receipt;
    }
    public function callbackId(array $request)
    {
        return $request['SaleOrderId'];
    }

    /**
     * Convert status to a readable message.
     *
     * @param $status
     * @return string
     */
    private function translateStatus($status): string
    {
        return match ($status) {
            '0' => 'تراکنش با موفقیت انجام شد',
            '11' => 'شماره کارت نامعتبر است',
            '12' => 'موجودی کافی نیست',
            '13' => 'رمز نادرست است',
            '14' => 'تعداد دفعات وارد کردن رمز بیش از حد مجاز است',
            '15' => 'کارت نامعتبر است',
            '16' => 'دفعات برداشت وجه بیش از حد مجاز است',
            '17' => 'کاربر از انجام تراکنش منصرف شده است',
            '18' => 'تاریخ انقضای کارت گذشته است',
            '19' => 'مبلغ برداشت وجه بیش از حد مجاز است',
            '111' => 'صادر کننده کارت نامعتبر است',
            '112' => 'خطای سوییچ صادر کننده کارت',
            '113' => 'پاسخی از صادر کننده کارت دریافت نشد',
            '114' => 'دارنده کارت مجاز به انجام این تراکنش نیست',
            '21' => 'پذیرنده نامعتبر است',
            '23' => 'خطای امنیتی رخ داده است',
            '24' => 'اطلاعات کاربری پذیرنده نامعتبر است',
            '25' => 'مبلغ نامعتبر است',
            '31' => 'پاسخ نامعتبر است',
            '32' => 'فرمت اطلاعات وارد شده صحیح نمی‌باشد',
            '33' => 'حساب نامعتبر است',
            '34' => 'خطای سیستمی',
            '35' => 'تاریخ نامعتبر است',
            '41' => 'شماره درخواست تکراری است',
            '42' => 'تراکنش Sale یافت نشد',
            '43' => 'قبلا درخواست Verify داده شده است',
            '44' => 'درخواست Verify یافت نشد',
            '45' => 'تراکنش Settle شده است',
            '46' => 'تراکنش Settle نشده است',
            '47' => 'تراکنش Settle یافت نشد',
            '48' => 'تراکنش Reverse شده است',
            '412' => 'شناسه قبض نادرست است',
            '413' => 'شناسه پرداخت نادرست است',
            '414' => 'سازمان صادر کننده قبض نامعتبر است',
            '415' => 'زمان جلسه کاری به پایان رسیده است',
            '416' => 'خطا در ثبت اطلاعات',
            '417' => 'شناسه پرداخت کننده نامعتبر است',
            '418' => 'اشکال در تعریف اطلاعات مشتری',
            '419' => 'تعداد دفعات ورود اطلاعات از حد مجاز گذشته است',
            '421' => 'IP نامعتبر است',
            '51' => 'تراکنش تکراری است',
            '54' => 'تراکنش مرجع موجود نیست',
            '55' => 'تراکنش نامعتبر است',
            '61' => 'خطا در واریز',
            '62' => 'مسیر بازگشت به سایت در دامنه ثبت شده برای پذیرنده قرار ندارد',
            '98' => 'سقف استفاده از رمز ایستا به پایان رسیده است',
            default => 'خطای ناشناخته رخ داده است.',
        };
    }
}

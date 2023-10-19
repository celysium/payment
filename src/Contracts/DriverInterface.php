<?php

namespace Celysium\Payment\Contracts;

use Celysium\Payment\GatewayForm;
use Celysium\Payment\Receipt;

interface DriverInterface
{
    /**
     * Create new purchase
     *
     * @param callable $callback
     */
    public function purchase(callable $callback): DriverInterface;

    /**
     * Pay the purchase
     *
     * @return GatewayForm
     */
    public function pay() : GatewayForm;

    /**
     * verify the payment
     *
     * @param array $request
     * @return Receipt
     */
    public function verify(array $request) : Receipt;

    /**
     * verify the payment
     *
     * @return Receipt
     */
    public function refund() : Receipt;


    public function callbackId(array $request);
}

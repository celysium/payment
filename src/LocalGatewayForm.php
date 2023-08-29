<?php

namespace Celysium\Payment;

class LocalGatewayForm extends GatewayForm
{

    /**
     * Retrieve default view path.
     *
     * @return string
     */
    public static function getDefaultViewPath(): string
    {
        return 'payment::local-pay';
    }
}

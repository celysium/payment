<?php

namespace Celysium\Payment;

use Celysium\Payment\Traits\Detailable;

/**
 * @property string $referenceId
 */

class Receipt
{
    use Detailable;

    public function __construct(protected string $referenceId)
    {
    }

    /**
     * Retrieve given value from details
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this?->$name;
    }
}

<?php

namespace Celysium\Payment;

use Exception;
use InvalidArgumentException;
use Celysium\Payment\Contracts\DriverInterface;
use Celysium\Payment\Exceptions\DriverNotFoundException;
use Celysium\Payment\Traits\Detailable;

/**
 * @property string $id
 * @property array $config
 * @property int $amount
 * @property string $transactionId
 * @property string $driver
 */

class Payment
{
    use Detailable;

    protected string $id;

    /**
     * Amount
     *
     * @var int
     */
    protected int $amount = 0;

    /**
     * payment transaction id
     *
     * @var string
     */
    protected string $transactionId;

    /**
     * @var string
     */
    protected string $driver;

    /**
     * @var object
     */
    protected object $config;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->loadConfig();
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

    /**
     * @param string $id
     * @return $this
     */
    public function id(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the amount of invoice
     *
     * @param int $amount
     * @return $this
     * @throws InvalidArgumentException
     */
    public function amount(int $amount): static
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Amount value should be a number.');
        }
        $this->amount = $amount;

        return $this;
    }

    /**
     * set transaction id
     *
     * @param string $id
     * @return $this
     */
    public function transactionId(string $id): static
    {
        $this->transactionId = $id;

        return $this;
    }

    /**
     * Set the value of driver
     *
     * @param string $driver
     * @return $this
     * @throws Exception
     */
    public function via(string $driver): static
    {
        $this->loadConfig($driver);

        return $this;
    }

    /**
     * @throws Exception
     */
    private function loadConfig($driver = null)
    {
        if ($driver == null) {
            $driver = config("payment.default");
        }
        $config = config("payment.drivers.$driver");
        if($config == null) {
            throw new DriverNotFoundException('Driver selected does not exist.');
        }
        $this->driver = $driver;
        $this->config = (object) $config;
    }

    /**
     * @param string $url
     * @return Payment
     */
    public function callback(string $url): static
    {
        $this->config->callbackUrl = $url;
        return $this;
    }

    /**
     * @return DriverInterface
     */
    public function gateway(): DriverInterface
    {
        return new $this->config->gateway($this);
    }
}

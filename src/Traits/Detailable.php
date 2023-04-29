<?php

namespace Celysium\Payment\Traits;

use Illuminate\Support\Arr;

trait Detailable
{
    protected array $details = [];

    /**
     * @param string|array $name
     * @param null $value
     * @return $this
     */
    public function detail(string|array $name, $value = null): static
    {
        $name = is_array($name) ? $name : [$name => $value];

        foreach ($name as $k => $v) {
            $this->details[$k] = $v;
        }

        return $this;
    }

    /**
     * Retrieve detail using its name
     *
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getDetail($name, $default = null): mixed
    {
        return $this->details[$name] ?? $default;
    }

    /**
     * Get the value of details
     */
    public function getDetails() : array
    {
        return $this->details;
    }
}
<?php

namespace Celysium\Payment;

class GatewayForm
{
    protected static string $viewPath;

    /**
     * Redirection form constructor.
     *
     * @param string $action
     * @param array $inputs
     * @param string $method
     */
    public function __construct(protected string $action, protected array $inputs = [], protected string $method = 'POST')
    {
    }

    /**
     * Retrieve default view path.
     *
     * @return string
     */
    public static function getDefaultViewPath(): string
    {
        return dirname(__DIR__) . '/resources/views/redirect-form.blade.php';
    }

    /**
     * Set view path
     *
     * @param string $path
     *
     * @return void
     */
    public static function setViewPath(string $path): void
    {
        static::$viewPath = $path;
    }

    /**
     * Retrieve view path.
     *
     * @return string
     */
    public static function getViewPath(): string
    {
        return static::$viewPath ?? static::getDefaultViewPath();
    }

    /**
     * Render form.
     *
     * @return string
     */
    public function render(): string
    {
        return view(static::getViewPath())
            ->with("action", $this->action)
            ->with("inputs", $this->inputs)
            ->with("method", $this->method)
            ->render();
    }

    /**
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'view'   => static::getViewPath(),
            'action' => $this->action,
            'inputs' => $this->inputs,
            'method' => $this->method,
        ];
    }

    /**
     * Retrieve string format of redirection form.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}

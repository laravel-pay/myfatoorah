<?php

namespace LaravelPay\MyFatoorah\Common\Traits;

trait HasLog
{
    /**
     * This is the file name or the logger object
     * It will be used in logging the payment/shipping events to help in debugging and monitor the process and connections.
     */
    protected string|object|null $loggerObj;

    /**
     * If $loggerObj is set as a logger object, you should set this var with the function name that will be used in the debugging.
     */
    protected ?string $loggerFunc;

    /**
     * It will log the payment/shipping process events
     *
     * @param  string  $msg It is the string message that will be written in the log file
     */
    public function log(string $msg): void
    {
        if (! $this->loggerObj) {
            return;
        }

        if (is_string($this->loggerObj)) {
            error_log(PHP_EOL.date('d.m.Y h:i:s').' - '.$msg, 3, $this->loggerObj);
        } elseif (method_exists($this->loggerObj, $this->loggerFunc)) {
            $this->loggerObj->{$this->loggerFunc}($msg);
        }
    }
}

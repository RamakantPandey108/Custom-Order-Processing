<?php
namespace SmartWork\CustomOrderProcessing\Logger;

use Monolog\Logger;

class CustomLogger extends Logger
{
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        parent::__construct($name, $handlers, $processors);
    }
}
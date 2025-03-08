<?php
namespace SmartWork\CustomOrderProcessing\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Handler\StreamHandler;

class Handler extends Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/custom_order_status.log';
}

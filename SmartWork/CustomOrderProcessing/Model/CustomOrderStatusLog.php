<?php

namespace SmartWork\CustomOrderProcessing\Model;

use Magento\Framework\Model\AbstractModel;

class CustomOrderStatusLog extends AbstractModel 
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\SmartWork\CustomOrderProcessing\Model\ResourceModel\CustomOrderStatusLog::class);
    }
}

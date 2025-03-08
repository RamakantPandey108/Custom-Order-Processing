<?php

namespace SmartWork\CustomOrderProcessing\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomOrderStatusLog extends AbstractDb
{
    /**
     * Define main table and primary key
     */
    protected function _construct()
    {
        $this->_init('custom_order_status_log', 'id');
    }
}

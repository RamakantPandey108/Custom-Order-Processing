<?php

namespace SmartWork\CustomOrderProcessing\Model\ResourceModel\CustomOrderStatusLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SmartWork\CustomOrderProcessing\Model\CustomOrderStatusLog as Model;
use SmartWork\CustomOrderProcessing\Model\ResourceModel\CustomOrderStatusLog as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Define Model & Resource Model
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}

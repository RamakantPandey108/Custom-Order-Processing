<?php
namespace SmartWork\CustomOrderProcessing\Api;

interface OrderStatusUpdateInterface
{
    /**
     * Update order status
     *
     * @param string $orderIncrementId
     * @param string $status
     * @return array[]
     */
    public function updateOrderStatus($orderIncrementId, $status);
}

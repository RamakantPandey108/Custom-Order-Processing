<?php
namespace SmartWork\CustomOrderProcessing\Model;

use SmartWork\CustomOrderProcessing\Api\OrderStatusUpdateInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use SmartWork\CustomOrderProcessing\Logger\CustomLogger;

class OrderStatusUpdate implements OrderStatusUpdateInterface
{
    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionFactory $orderStatusCollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * @var CustomLogger $logger
     */
    protected $logger;


    /**
     * Constructor.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     * @param \SmartWork\CustomOrderProcessing\Logger\CustomLogger $logger
     */
    public function __construct(    
        OrderRepositoryInterface $orderRepository, 
        SearchCriteriaBuilder $searchCriteriaBuilder, 
        CollectionFactory $orderStatusCollectionFactory,
        CustomLogger $logger
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Update order status
     *
     * @param string $orderIncrementId
     * @param string $status
     * @return array[]
     */
    public function updateOrderStatus($orderIncrementId, $status)
    {
        try {
            // Load the order by increment ID
            $order = $this->getOrderByIncrementId($orderIncrementId);

            // Validate allowed status transition
            // if (!$order->canUnhold()) {
                // throw new LocalizedException(__('Order cannot be updated.'));
            // }
            if (!$this->isStatusTransitionAllowed($order, $status)) {
                return [    
                    [
                        'status' => false,
                        'message' => __('Status transition not allowed.')
                    ]
                ];
            }
            $order->setStatus($status);
            $this->orderRepository->save($order);
            return [    
                [
                    'status' => true,
                    'message' => __('Order status updated successfully.')
                ]
            ];
            return __('Order status updated successfully.');
        } catch (NoSuchEntityException $e) {
            $this->logger->error("Order does not exist.: " . $e->getMessage());
            return [    
                [
                    'status' => false,
                    'message' => __('Order does not exist.')
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error updating order status.: " . $e->getMessage());
            return [    
                [
                    'status' => false,
                    'message' => __('Error updating order status.')
                ]
            ];
        }
    }

    /**
     * Retrieve order by increment ID.
     *
     * @param string $incrementId The order increment ID.
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderByIncrementId($incrementId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $incrementId, 'eq')
                ->create();

            $orderList = $this->orderRepository->getList($searchCriteria);
            $items = $orderList->getItems();

            if (!empty($items)) {
                return reset($items); // Returns the first order found
            }
            
            throw new NoSuchEntityException(__('Order not found.'));
        } catch (NoSuchEntityException $e) {
            $this->logger->error("Error in getting  order from Increment id: " . $e->getMessage());
            return null; // Order not found
        }
    }

    /**
     * Check if the status transition is allowed for an order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $newStatus The new status to transition to.
     * @return bool True if the transition is allowed, otherwise false.
     */
    public function isStatusTransitionAllowed($order, $newStatus)
    {   
        // Get current state & status
        $currentState = $order->getState();
        $currentStatus = $order->getStatus();
        $coll = $this->orderStatusCollectionFactory->create();
        $coll->joinStates();
        $coll->addFieldToFilter('state_table.status', ['eq'=>$newStatus]);
        $coll->addFieldToFilter('state',['eq'=>$currentState]);
        if ($coll->count()) {
            return true; // Transition is allowed
        }
        return false; // Transition not allowed
    }
}

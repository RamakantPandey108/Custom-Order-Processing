<?php
namespace SmartWork\CustomOrderProcessing\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mail\Template\TransportBuilder;
use SmartWork\CustomOrderProcessing\Model\CustomOrderStatusLogFactory;
use SmartWork\CustomOrderProcessing\Logger\CustomLogger;

class OrderStatusObserver implements ObserverInterface
{
    /**
     * @var CustomOrderStatusLogFactory
     */
    protected $customOrderStatusLogFactory;

    /**
     * @var ResourceConnection $resource
     */
    protected $resource;
    
    /**
     * @var TransportBuilder $transportBuilder
     */
    protected $transportBuilder;

    /**
     * @var CustomLogger $logger
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param SmartWork\CustomOrderProcessing\Model\CustomOrderStatusLogFactory $customOrderStatusLogFactory
     * @param \SmartWork\CustomOrderProcessing\Logger\CustomLogger $logger
     */
    public function __construct(
        ResourceConnection $resource,
        TransportBuilder $transportBuilder,
        CustomOrderStatusLogFactory $customOrderStatusLogFactory,
        CustomLogger $logger
    ) {
        $this->resource = $resource;
        $this->transportBuilder = $transportBuilder;
        $this->customOrderStatusLogFactory = $customOrderStatusLogFactory;
        $this->logger = $logger;
    }

    /**
     * Execute observer to log order status changes and send email on shipment.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();

        // Create a new log entry
        $logEntry = $this->customOrderStatusLogFactory->create();
        $logEntry->setOrderId($order->getId());
        $logEntry->setOldStatus($oldStatus);
        $logEntry->setNewStatus($newStatus);
        $logEntry->setTimestamp((new \DateTime())->format('Y-m-d H:i:s'));

        $logEntry->save();

        // If order is shipped, send email
        if ($newStatus == 'shipped') {
            $this->sendEmail($order);
        }
    }

    /**
     * Send shipment email to the customer.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function sendEmail($order)
    {
        try {
            $this->logger->info("Email Function run");
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('sales_email_shipment_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $order->getStoreId()
                ])
                ->setTemplateVars(['order' => $order])
                ->setFrom('general')
                ->addTo($order->getCustomerEmail())
                ->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            // Handle email error
        }
    }
}

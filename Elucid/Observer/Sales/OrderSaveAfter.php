<?php

namespace PixieMedia\Elucid\Observer\Sales;

use Magento\Framework\Event\Observer;
use PixieMedia\Elucid\Helper\Elucid;
use Psr\Log\LoggerInterface;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Elucid Helper.
     *
     * @var Elucid
     */
    protected $eHelper;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * OrderSaveAfter constructor.
     *
     * @param Elucid $eHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Elucid $eHelper,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->eHelper = $eHelper;
    }

    /**
     * Execute Observer.
     *
     * @param Observer $observer
     * @return bool
     */
    public function execute(Observer $observer)
    {
        $this->logger->critical('---> step 1: Elucid');

        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        if ($orderId) {
            $this->logger->critical('---> step 2: Elucid');

            try {
                $this->logger->critical('---> step 3: Elucid');
                $this->eHelper->addOrderToQueue($order);
                $this->logger->critical('---> step 4: Elucid');
                $this->logThis("GENERATED: " . $orderId);
                $this->logger->critical('---> step 5: Elucid');
            } catch (Exception $e) {
                $this->logger->critical('---> step 6: Elucid');
                $this->logThis("ERROR: COULD NOT GENERATE: " . $orderId);
            }
        } else {
            $this->logger->critical('---> step 7: Elucid');
            $this->logThis("ERROR: MISSING ORDER NUMBER");
        }

        return true;
    }

    /**
     * Write to log file.
     *
     * @param $msg
     */
    public function logThis($msg)
    {
        $this->logger->critical('---> Elucid', ['message', $msg]);

        //        $writer = new \Zend\Log\Writer\Stream($_SERVER['DOCUMENT_ROOT'] . '/var/log/elucid.log');
        //        $logger = new \Zend\Log\Logger();
        //        $logger->addWriter($writer);
        //        $logger->info($msg);
    }
}

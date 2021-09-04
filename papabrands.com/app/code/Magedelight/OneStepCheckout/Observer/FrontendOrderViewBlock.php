<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OneStepCheckout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\TemplateFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class FrontendOrderViewBlock
 * @package Magedelight\OneStepCheckout\Observer
 */
class FrontendOrderViewBlock implements ObserverInterface
{
    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * FrontendOrderViewBlock constructor.
     * @param TemplateFactory $templateFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TemplateFactory $templateFactory,
        TimezoneInterface $timezone
    ) {
        $this->templateFactory = $templateFactory;
        $this->timezone = $timezone;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $element = $observer->getElementName();
        if ($element == 'sales.order.info') {
            $orderViewBlock = $observer->getLayout()->getBlock($element);
            $order = $orderViewBlock->getOrder();

            if ($order->getMdOscDeliveryDate() != '0000-00-00') {
                // $formattedDate = $this->timezone->formatDate(
                //     $order->getMdOscDeliveryDate(),
                //     \IntlDateFormatter::MEDIUM
                // );
                $formattedDate = date("d M Y", strtotime($order->getMdOscDeliveryDate()));
            } else {
                $formattedDate = '';
            }

            /** @var \Magento\Framework\View\Element\Template $deliveryDateBlock */
            $deliveryDateBlock = $this->templateFactory->create();
            $deliveryDateBlock->setMdOscDeliveryDate($formattedDate);
            $deliveryDateBlock->setMdOscDeliveryTime($order->getMdOscDeliveryTime());
            $deliveryDateBlock->setMdOscDeliveryComment($order->getMdOscDeliveryComment());
            $deliveryDateBlock->setTemplate('Magedelight_OneStepCheckout::delivery_date_shipping_info.phtml');
            $html = $observer->getTransport()->getOutput() . $deliveryDateBlock->toHtml();
            $observer->getTransport()->setOutput($html);
        }
        return $this;
    }
}

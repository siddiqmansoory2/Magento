<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Bluedart\Controller\Bluedart;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Psr\Log\LoggerInterface as Logger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Softprodigy\Bluedart\Model\Mail\TransportBuilder;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
/**
 * Description of Sendshipment
 *
 * @author mannu
 */
class CheckOrderItem extends \Magento\Backend\App\Action {

    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'contacts/email/email_template';
    const XML_PATH_EMAIL_PDF_SENDER = 'sales_email/invoice/pdf_sender';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @var Softprodigy\Bluedart\Helper\Data 
     */
    protected $__helper;
    protected $messageManager;
    protected $transportBuilder;
    protected $orderModel;
    
    
    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    Context $context, PageFactory $resultPageFactory, 
            ShipmentSender $shipmentSender,
            \Magento\Sales\Model\Order $orderModel,
            \Magento\Framework\Message\ManagerInterface $messageManager, \Softprodigy\Bluedart\Helper\Data $__helper, Logger $logger, TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->__helper = $__helper;
        $this->messageManager = $messageManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->orderModel = $orderModel;
        //$this->shipmentLoader = $shipmentLoader;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed() {
        return true;
        return $this->_authorization->isAllowed('Softprodigy_Bluedart::Bluedart');
    }
    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {
		
			$_uitems='';
			
			$order = $this->orderModel->load(94491);
			
			foreach ($order->getAllVisibleItems() AS $orderItem) {
				
				
				$shipping_label='';
				$_uitems .='<tr style="display: flex; margin-bottom: 10px;">
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 40%;">'.$orderItem->getName().' '.$orderItem->getSku().'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.($orderItem->getQtyOrdered()*1).'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.$order->getOrderCurrencyCode().' '.($orderItem->getPrice()*1).'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.$shipping_label.'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.$order->getOrderCurrencyCode().' '.($orderItem->getPrice()*$orderItem->getQtyOrdered()*1).'</td>
				</tr>';
				
				
			} 
			echo "<table>".$_uitems."</table>";
        
    }
}

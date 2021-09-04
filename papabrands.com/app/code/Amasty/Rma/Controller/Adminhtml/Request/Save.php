<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Controller\Adminhtml\Request;

use Amasty\Rma\Api\Data\MessageInterface;
use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\Rma\Api\Data\RequestItemInterface;
use Amasty\Rma\Api\RequestRepositoryInterface;
use Amasty\Rma\Controller\Adminhtml\RegistryConstants;
use Amasty\Rma\Model\Chat\ResourceModel\CollectionFactory as MessageCollectionFactory;
use Amasty\Rma\Api\StatusRepositoryInterface;
use Amasty\Rma\Model\ConfigProvider;
use Amasty\Rma\Model\OptionSource\Grid;
use Amasty\Rma\Model\Request\Email\EmailRequest;
use Amasty\Rma\Observer\RmaEventNames;
use Amasty\Rma\Utils\Email;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Rma::request_save';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RequestRepositoryInterface
     */
    private $repository;

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var Grid
     */
    private $grid;

    /**
     * @var MessageCollectionFactory
     */
    private $messageCollectionFactory;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var EmailRequest
     */
    private $emailRequest;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        Context $context,
        RequestRepositoryInterface $repository,
        MessageCollectionFactory $messageCollectionFactory,
        DataPersistorInterface $dataPersistor,
        EmailRequest $emailRequest,
        ConfigProvider $configProvider,
        DataObject $dataObject,
        ScopeConfigInterface $scopeConfig,
        StatusRepositoryInterface $statusRepository,
        Email $email,
        Grid $grid
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->repository = $repository;
        $this->statusRepository = $statusRepository;
        $this->grid = $grid;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->email = $email;
        $this->emailRequest = $emailRequest;
        $this->configProvider = $configProvider;
        $this->scopeConfig = $scopeConfig;
        $this->dataObject = $dataObject;
        $this->eventManager = $context->getEventManager() ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Event\ManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->getRequest()->getParams()) {
            try {
                if (!($requestId = (int)$this->getRequest()->getParam(RegistryConstants::REQUEST_ID))) {
                    return $this->_redirect('*/*/pending');
                }

                $model = $this->repository->getById($requestId);
                $this->processItems($model, $this->getRequest()->getParam('return_items'));
                $originalStatus = $model->getStatus();

                if ($status = $this->getRequest()->getParam(RequestInterface::STATUS)) {
                    $model->setStatus($status);
                }
				
				
				$_getParam=$this->getRequest()->getParams();
				
				$_getParam['information']['customer']['email'];
				
				$refund_items=array();
				
				
				foreach($_getParam['return_items'] as $_return_items){
					
					foreach($_return_items as $__return_items){
					
						if($__return_items['status']==1){
							$refund_items[]=$__return_items['name'];
						}
					
					}
					
				}
				$this->_sobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$UrlInterface = $this->_sobjectManager->create('\Magento\Framework\UrlInterface');
				
				$_body="<!doctype html><html><head><meta http-equiv='Content-type' content='text/html; charset=utf-8' /><meta content='telephone=no' name='format-detection' /><title>Papa Brands</title></head><body class='body' style='padding:0 !important; margin:0 !important; display:block !important; background:#ffffff; -webkit-text-size-adjust:none;'><table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td align='center' valign='top'><table width='700' border='0' cellspacing='0' cellpadding='0'><tr><td style='background: #11304c; padding: 30px 20px;'> <img src='".rtrim($UrlInterface->getUrl('images/papa-logo.png'), "/")."' alt='logo' style='margin: auto; display: block; max-width: 70%;'></td></tr><tr><td style='border: 2px solid #acad94; padding: 20px; width: 100%;'><table width='100%' border='0' cellspacing='0' cellpadding='0' style='border: 2px solid #acad94'><tr><td style='padding: 15px 30px; width: 100%;'><table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 10px;'> Greetings ".$_getParam['information']['customer']['name'].",</p></td></tr><tr><td style='width: 100%;'><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 10px; margin-top: 5px;'> This is a notification toconfirm that we  have successfully processed a refund for your purchase of ".implode(", ",$refund_items).". This refund applies to order number # ".$_getParam['order_id'].". Please note that it may take up to 5 business days for the money to appear back in your account.</p></td></tr><tr><td><p style='font-size: 1.1rem; font-weight: 600;'> Regards, <br> Papa Brands.</p></td></tr><tr><td><p style='font-size: 1.1rem; font-weight: 600; text-align: center; line-height: 25px; margin-bottom: 20px;'> If you have any queries, contact us at: support@papabrands.com or call/whatsapp us at: +91 9372221906</p></td></tr></table></td></tr></table></td></tr></table></td></tr></table></body></html>";
				
				
				if(count($refund_items)>0){
					
					$curl = curl_init();
					curl_setopt_array($curl, array(
					  CURLOPT_URL => 'https://app.yellowmessenger.com/api/engagements/notifications/v2/push?bot=x1627030069843',
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => '',
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => false,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => 'POST',
					  CURLOPT_POSTFIELDS =>'{
							"userDetails": {
							"email": "'.$_getParam['information']['customer']['email'].'"
						},
						"notification": {
							"type": "email",
							"subject": "Refund process",
							"sender": "support@papabrands.com",
							"freeTextContent": "'.$_body.'"
							}
						}',
						CURLOPT_HTTPHEADER => array(
							'x-auth-token: 5deabcd62f4191d541850fae2d6633188e208d5d8b7a1f7a11d898da73d169ae',
							'Content-Type: application/json'
						),
					));
					$response = curl_exec($curl);
					curl_close($curl);
					
					$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ResetPassword.log');
					$logger = new \Zend\Log\Logger();
					$logger->addWriter($writer);
					
					$logger->info('{
							"userDetails": {
							"email": "'.$_getParam['information']['customer']['email'].'"
						},
						"notification": {
							"type": "email",
							"subject": "Refund process",
							"sender": "support@papabrands.com",
							"freeTextContent": "Greetings '.$_getParam['information']['customer']['name'].', This is a notification toconfirm that we  have successfully processed a refund for your purchase of '.implode(", ",$refund_items).'. This refund applies to order number# '.$_getParam['order_id'].'. Please note that it may take up to 5 business days for the money to appear back in your account. Regards,Papa Brands."						}
						}');
					$logger->info($response);
					
				}
				
				/*echo "<pre>";print_r($this->getRequest()->getParams()); die;*/
				
				
				
                $model->setManagerId($this->getRequest()->getParam(RequestInterface::MANAGER_ID));

                if ($note = $this->getRequest()->getParam(RequestInterface::NOTE)) {
                    $model->setNote($note);
                }

                $origStatus = (int)$model->getOrigData(RequestInterface::STATUS);
                $this->repository->save($model);
                $this->eventManager->dispatch(
                    RmaEventNames::RMA_SAVED_BY_MANAGER,
                    ['request' => $model]
                );

                if ($origStatus === $model->getStatus()
                    && $this->configProvider->isNotifyCustomerAboutNewMessage($model->getStoreId())
                ) {
                    $messageCollection = $this->messageCollectionFactory->create();
                    $messagesCount = $messageCollection
                        ->addFieldToFilter(MessageInterface::REQUEST_ID, $model->getRequestId())
                        ->addFieldToFilter(
                            MessageInterface::MESSAGE_ID,
                            ['gt' => $this->getRequest()->getParam('last_message_id', 0)]
                        )->addFieldToFilter(MessageInterface::IS_MANAGER, 1)
                        ->addFieldToFilter(MessageInterface::IS_READ, 0)
                        ->getSize();

                    if ($messagesCount) {
                        $emailRequest = $this->emailRequest->parseRequest($model);
                        $storeId = $model->getStoreId();
                        $this->email->sendEmail(
                            $emailRequest->getCustomerEmail(),
                            $storeId,
                            $this->scopeConfig->getValue(
                                ConfigProvider::XPATH_NEW_MESSAGE_TEMPLATE,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $storeId
                            ),
                            ['email_request' => $emailRequest],
                            \Magento\Framework\App\Area::AREA_FRONTEND,
                            $this->configProvider->getChatSender($storeId)
                        );
                    }
                }

                $this->messageManager->addSuccessMessage(__('You saved the return request.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->getOriginalGrid($status, $originalStatus);

                    return $this->_redirect('*/*/view', [RegistryConstants::REQUEST_ID => $model->getId()]);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                //TODO $this->dataPersistor->set(RegistryConstants::REQ, $data);

                return $this->_redirect('*/*/view', [RegistryConstants::REQUEST_ID => $requestId]);
            }
        }

        $returnGrid = $this->getOriginalGrid($status, $originalStatus);

        return $this->_redirect("*/*/$returnGrid");
    }

    /**
     * @param int $status
     * @param int $originalStatus
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getOriginalGrid($status, $originalStatus)
    {
        $newGridId = $this->statusRepository->getById($status)->getGrid();
        $originalGridId = $this->statusRepository->getById($originalStatus)->getGrid();

        if (!$returnGrid = $this->_session->getAmRmaOriginalGrid()) {
            switch ($originalGridId) {
                case Grid::MANAGE:
                    $returnGrid = 'manage';
                    break;
                case Grid::PENDING:
                    $returnGrid = 'pending';
                    break;
                case Grid::ARCHIVED:
                    $returnGrid = 'archive';
                    break;
            }

            $this->_session->setAmRmaOriginalGrid($returnGrid);
        }

        if ($newGridId !== $originalGridId) {
            $newGrid = $this->grid->toArray()[$newGridId];
            $this->messageManager->addNoticeMessage(
                __('The return request has been moved to %1 grid.', $newGrid)
            );
        }

        return $returnGrid;
    }

    public function processItems(\Amasty\Rma\Api\Data\RequestInterface $model, $items)
    {
        $resultItems = [];

        $currentRequestItems = [];

        foreach ($model->getRequestItems() as $requestItem) {
            if (empty($currentRequestItems[$requestItem->getOrderItemId()])) {
                $currentRequestItems[$requestItem->getOrderItemId()] = [];
            }

            $currentRequestItems[$requestItem->getOrderItemId()][$requestItem->getRequestItemId()] = $requestItem;
        }

        foreach ($currentRequestItems as $currentRequestItem) {
            $currentItems = false;
            $requestQty = 0;

            foreach ($items as $item) {
                if (!empty($item[0]) && !empty($item[0][RequestItemInterface::REQUEST_ITEM_ID])
                    && !empty($currentRequestItem[(int)$item[0][RequestItemInterface::REQUEST_ITEM_ID]])
                ) {
                    $currentItems = $item;
                    $requestQty = $currentRequestItem[(int)$item[0][RequestItemInterface::REQUEST_ITEM_ID]]
                        ->getRequestQty();
                    break;
                }
            }

            if ($currentItems) {
                $rowItems = [];

                foreach ($currentItems as $currentItem) {
                    $currentItem = $this->dataObject->unsetData()->setData($currentItem);

                    if (!empty($currentItem->getData(RequestItemInterface::REQUEST_ITEM_ID))
                        && ($requestItem = $currentRequestItem[
                            $currentItem->getData(RequestItemInterface::REQUEST_ITEM_ID)
                        ])
                    ) {
                        $requestItem->setQty($currentItem->getData(RequestItemInterface::QTY))
                            ->setItemStatus($currentItem->getData('status'))
                            ->setResolutionId($currentItem->getData(RequestItemInterface::RESOLUTION_ID))
                            ->setConditionId($currentItem->getData(RequestItemInterface::CONDITION_ID))
                            ->setReasonId($currentItem->getData(RequestItemInterface::REASON_ID));
                        $rowItems[] = $requestItem;
                    } else {
                        $splitItem = $this->repository->getEmptyRequestItemModel();
                        $splitItem->setRequestId($requestItem->getRequestId())
                            ->setOrderItemId($requestItem->getOrderItemId())
                            ->setQty($currentItem->getData(RequestItemInterface::QTY))
                            ->setItemStatus($currentItem->getData('status'))
                            ->setResolutionId($currentItem->getData(RequestItemInterface::RESOLUTION_ID))
                            ->setConditionId($currentItem->getData(RequestItemInterface::CONDITION_ID))
                            ->setReasonId($currentItem->getData(RequestItemInterface::REASON_ID));
                        $rowItems[] = $splitItem;
                    }
                }

                $newQty = 0;

                foreach ($rowItems as $rowItem) {
                    $newQty += $rowItem->getQty();
                    $resultItems[] = $rowItem;
                }

                if ($newQty != $requestQty) {
                    throw new LocalizedException(__('Wrong Request Qty'));
                }
            } elseif (!empty($currentRequestItem[0])) {
                $resultItems[] = $currentRequestItem[0];
            }
        }

        $model->setRequestItems($resultItems);
    }
}

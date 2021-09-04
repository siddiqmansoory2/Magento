<?php

namespace Dolphin\Walletrewardpoints\Controller\Withdraw;

use Dolphin\Walletrewardpoints\Model\Sales\Total\CreditDiscount;
use Dolphin\Walletrewardpoints\Model\Withdraw;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Request extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context,
        Withdraw $withdraw,
        JsonFactory $resultJsonFactory,
        CreditDiscount $creditDiscountModel
    ) {
        $this->withdraw = $withdraw;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->creditDiscountModel = $creditDiscountModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = (array) $this->getRequest()->getPost();
        if ($data) {
            $data['status'] = 0;
            $data['requested_date'] = date("Y-m-d H:i:s");
            $data['updated_date'] = date("Y-m-d H:i:s");
            $data['credit'] = $this->creditDiscountModel->convertPrice($data['credit'], 1);
            $model = $this->withdraw->setData($data);
            $model->save();
        }
        $this->messageManager->addSuccess(
            __(
                'Your withdraw request submitted successfully.
                We will review your request and accept it as soon as possible.'
            )
        );
        return $resultRedirect->setPath('walletrewardpoints/withdraw/form');
    }
}

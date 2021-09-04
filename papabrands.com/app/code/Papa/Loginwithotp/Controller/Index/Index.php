<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Loginwithotp\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session');
		$this->url = $this->_objectManager->get('\Magento\Framework\UrlInterface');
		$this->http = $this->_objectManager->get('\Magento\Framework\App\Response\Http');
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            $this->http->setRedirect($this->url->getUrl('customer/account/login'), 301);
        }
		
		
		return $this->resultPageFactory->create();
    }
}


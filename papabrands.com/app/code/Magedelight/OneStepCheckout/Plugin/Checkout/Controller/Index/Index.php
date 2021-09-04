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

namespace Magedelight\OneStepCheckout\Plugin\Checkout\Controller\Index;

use Closure;
use Magedelight\OneStepCheckout\Helper\Data;
use Magento\Checkout\Block\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;

class Index extends \Magento\Checkout\Controller\Index\Index
{
    const ONE_STEP_CHECKOUT_LAYOUT = 'onestepcheckout';
    const ONE_STEP_CHECKOUT_HEADER_FOOTER_LAYOUT = 'onestepcheckout_header_footer';
    const ONE_STEP_CHECKOUT_EXTRA_FEE_LAYOUT = 'onestepcheckout_extrafee';

    /**
     * @var Data
     */
    private $oscHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param Registry $coreRegistry
     * @param InlineInterface $translateInline
     * @param Validator $formKeyValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param PageFactory $resultPageFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $oscHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        Registry $coreRegistry,
        InlineInterface $translateInline,
        Validator $formKeyValidator,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        CartRepositoryInterface $quoteRepository,
        PageFactory $resultPageFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        Data $oscHelper
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );
        $this->oscHelper = $oscHelper;
    }

    /**
     * @param \Magento\Checkout\Controller\Index\Index $subject
     * @param Closure $proceed
     * @return Redirect|Page|mixed
     */
    public function afterExecute(\Magento\Checkout\Controller\Index\Index $subject, $result)
    {
        if ($this->oscHelper->isModuleEnable()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->getUpdate()->addHandle(self::ONE_STEP_CHECKOUT_HEADER_FOOTER_LAYOUT);
            if ($this->oscHelper->isExtraFeeEnabled()) {
                $resultPage->getLayout()->getUpdate()->addHandle(self::ONE_STEP_CHECKOUT_EXTRA_FEE_LAYOUT);
            }
            $resultPage->getLayout()->getUpdate()->addHandle(self::ONE_STEP_CHECKOUT_LAYOUT);
            $this->showHeaderFooter($resultPage->getLayout());
            // Meta Title
            $title = $this->oscHelper->getCheckoutMetaTitle() ? $this->oscHelper->getCheckoutMetaTitle() : 'Checkout';
            /** @var Onepage $checkoutBlock */
            $checkoutBlock = $resultPage->getLayout()->getBlock('checkout.root');
            $checkoutBlock->setTemplate('Magedelight_OneStepCheckout::onestepcheckout.phtml')
                ->setData('osc_helper', $this->oscHelper);

            $resultPage->getConfig()->getTitle()->set(__($title));
            return $resultPage;
        } else {
            return $result;
        }
    }

    /**
     * @param $layout
     */
    private function showHeaderFooter($layout)
    {
        if ($this->oscHelper->showHeader()) {
            $layout->getUpdate()->addHandle('onestepcheckout_show_header');
            if (!$this->oscHelper->showFooter()) {
                $layout->unsetElement('footer-container');
            }
        } else {
            if ($this->oscHelper->showFooter()) {
                $layout->getUpdate()->addHandle('onestepcheckout_show_footer');
            }
        }
    }
}

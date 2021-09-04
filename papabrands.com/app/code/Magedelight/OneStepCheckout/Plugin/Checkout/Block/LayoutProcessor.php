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

namespace Magedelight\OneStepCheckout\Plugin\Checkout\Block;

use Magento\Framework\Stdlib\ArrayManager;
use Magedelight\OneStepCheckout\Helper\Data;
use Magedelight\OneStepCheckout\Model\Address\Form\DefaultSortOrder;
use Amazon\Core\Helper\Data as AmazonHelper;
use Magento\Checkout\Model\Session;
use Magedelight\OneStepCheckout\Model\JsLayoutAccessData as AccessData;
use Magedelight\OneStepCheckout\Model\JsLayoutAccessDataFactory as AccessDataFactory;

class LayoutProcessor
{
    const TEMPLATE_PATH = 'components.checkout.config.template';
    const DEFAULT_DISCOUNT_BLOCK = 'components.checkout.children.steps.children.billing-step.children.payment.children.afterMethods.children.discount';
    const CART_ITEMS_COMPONENT = 'components.checkout.children.sidebar.children.summary.children.cart_items';
    const CART_ITEM_THUMBNAIL_COMPONENT = self::CART_ITEMS_COMPONENT.'.children.details.children.thumbnail.component';
    const BEFORE_OSC_BUTTON = 'components.checkout.children.before-osc-button';
    const COMMENTS_BLOCK = self::BEFORE_OSC_BUTTON.'.children.comments';
    const NEWSLETTER_BLOCK = self::BEFORE_OSC_BUTTON.'.children.newsletter';
    const DISCOUNT_BLOCK = self::BEFORE_OSC_BUTTON.'.children.discount';
    const SHIPPING_STEP = 'components.checkout.children.steps.children.shipping-step';
    const BILLING_STEP = 'components.checkout.children.steps.children.billing-step';
    const DEFAULT_BEFORE_PLACE_ORDER_BLOCK = self::BILLING_STEP.'.children.payment.children.payments-list.children.before-place-order.children';
    const DEFAULT_AGREEMENT_BLOCK = self::BILLING_STEP.'.children.payment.children.payments-list.children.before-place-order.children.agreements';
    const SIDEBAR_AGREEMENT_BLOCK = self::BEFORE_OSC_BUTTON.'.children.agreements';
    const AGREEMENT_BLOCK_VALIDATOR = self::BILLING_STEP.'.children.payment.children.additional-payment-validators.children.agreements-validator.component';
    const SHIPPING_METHOD = 'components.checkout.children.steps.children.shippingMethods';
    const DELIVERY_DATE_BLOCK = self::SHIPPING_METHOD.'.children.mdosc-delivery-date';
    const EXTRA_FEE_BLOCK = self::BEFORE_OSC_BUTTON.'.children.extraFee';
    const ORDER_LEVEL_GIFT_MESSAGE = self::BEFORE_OSC_BUTTON.'.children.giftMessage';
    const ITEM_LEVEL_GIFT_MESSAGE = self::CART_ITEMS_COMPONENT.'.children.details.children';
    const DEFAULT_PLACE_ORDER_BUTTON = self::BEFORE_OSC_BUTTON.'.children.place-order-button';
    const PAYMENT_PLACE_ORDER_BUTTON = self::BILLING_STEP.'.children.payment.children.payments-list.children.before-place-order.children.place-order-button';
    const CUSTOMER_EMAIL_BLOCK = self::SHIPPING_STEP.'.children.shippingAddress.children.customer-email';

    /**
     * @var \Magedelight\OneStepCheckout\Helper\Data
     */
    private $oscHelper;

    /**
     * @var AmazonHelper
     */
    private $amazonHelper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param \Magedelight\OneStepCheckout\Helper\Data $oscHelper
     */
    public function __construct(
        Data $oscHelper,
        ArrayManager $arrayManager,
        DefaultSortOrder $defaultSortOrder,
        AmazonHelper $amazonHelper,
        Session $checkoutSession,
        AccessDataFactory $accessData
    ) {
        $this->oscHelper = $oscHelper;
        $this->arrayManager = $arrayManager;
        $this->defaultSortOrder = $defaultSortOrder;
        $this->amazonHelper = $amazonHelper;
        $this->checkoutSession = $checkoutSession;
        $this->accessData = $accessData;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $jsLayout)
    {
        if (!$this->oscHelper->isModuleEnable()) {
            return $jsLayout;
        }
        $quote = $this->checkoutSession->getQuote();
        
        $data = $this->accessData->create(['data' => $jsLayout]);
        if ($this->oscHelper->getLayout() == '2column') {
            $this->changeLayout($data);
        }
        $this->setCartItemBlock($data);
        $this->removeBlock($data, $quote);
        if ($this->oscHelper->showAgreements() == 'sidebar') {
            $this->changeAgreementBlock($data);
        }
        if ($this->oscHelper->isDeliveryDateEnabled()) {
            $data->setArray(self::DELIVERY_DATE_BLOCK, $this->getDeliveryLayout());
        }
        if ($this->oscHelper->isGiftMessageItemLevel()) {
            $this->setItemLevelGiftMessage($data, $quote);
        }
        if (!empty($this->oscHelper->getStepConfig())) {
            $steps = $this->oscHelper->getStepConfig();
            $this->setStepSortOrder($data, $steps);
        }
        if ($this->oscHelper->getPlaceOrderPosition() == 'payment') {
            $data->removeArray(self::DEFAULT_PLACE_ORDER_BUTTON);
        } else {
            $data->removeArray(self::PAYMENT_PLACE_ORDER_BUTTON);
        }
        if ($this->oscHelper->isRegistrationEnabled()) {
            $this->setRegistrationBlock($data);
        }
        if ($this->oscHelper->getBillingAddressBlock() == 'shipping') {
            $this->changeBillingAddressBlock($data);
        }
//        $this->FixedAmazonePay($data, $quote);
//        $this->FixedStorePickup($data);

        $jsLayout = $data->exportArray();
        return $jsLayout;
    }

    /**
     * Change the cart item component from the order review tab
     * @param AccessData $data
     */
    private function setCartItemBlock(AccessData $data)
    {
        if ($data->hasArray(self::CART_ITEMS_COMPONENT)) {
            $data->setArray(
                self::CART_ITEMS_COMPONENT.'.component',
                "Magedelight_OneStepCheckout/js/view/summary/cart-items"
            );
            $data->setArray(
                self::CART_ITEMS_COMPONENT.'.displayArea',
                "item-review"
            );
        }
        if ($data->hasArray(self::CART_ITEMS_COMPONENT.'.children.details.component')) {
            $data->setArray(
                self::CART_ITEMS_COMPONENT.'.children.details.component',
                'Magedelight_OneStepCheckout/js/view/summary/item/details'
            );
        }
        if ($data->hasArray(self::CART_ITEM_THUMBNAIL_COMPONENT)) {
            $data->setArray(
                self::CART_ITEM_THUMBNAIL_COMPONENT,
                'Magedelight_OneStepCheckout/js/view/summary/item/details/thumbnail'
            );
        }
    }

    /**
     * Change the layout file for the 2-column
     * @param AccessData $data
     */
    private function changeLayout(AccessData $data)
    {
        $data->setArray(
            self::TEMPLATE_PATH,
            'Magedelight_OneStepCheckout/onestepcheckout-2column'
        );
    }

    /**
     * Default discount block remove from the below payment method
     * @param AccessData $data
     */
    private function removeDiscountBlock(AccessData $data)
    {
        if ($data->hasArray(self::DEFAULT_DISCOUNT_BLOCK)) {
            $data->removeArray(self::DEFAULT_DISCOUNT_BLOCK);
        }
    }

    /**
     * Remove Block
     * @param AccessData $data
     * @param $quote
     */
    private function removeBlock(AccessData $data, $quote)
    {
        $this->removeDiscountBlock($data);
        if (!$this->oscHelper->showComments()) {
            if ($data->hasArray(self::COMMENTS_BLOCK)) {
                $data->removeArray(self::COMMENTS_BLOCK);
            }
        }
        if (!$this->oscHelper->showNewsletter()) {
            if ($data->hasArray(self::NEWSLETTER_BLOCK)) {
                $data->removeArray(self::NEWSLETTER_BLOCK);
            }
        }
        if (!$this->oscHelper->showDiscountCoupon()) {
            if ($data->hasArray(self::DISCOUNT_BLOCK)) {
                $data->removeArray(self::DISCOUNT_BLOCK);
            }
        }
        if (!$this->oscHelper->showProductThumbnail()) {
            if ($data->hasArray(self::CART_ITEMS_COMPONENT.'.children.details.children.thumbnail')) {
                $data->removeArray(self::CART_ITEMS_COMPONENT.'.children.details.children.thumbnail');
            }
        }
        if (!$this->oscHelper->isGiftMessageOrderLevel()) {
            if ($data->hasArray(self::ORDER_LEVEL_GIFT_MESSAGE)) {
                $data->removeArray(self::ORDER_LEVEL_GIFT_MESSAGE);
            }
        }
        /* @var $quote \Magento\Quote\Model\Quote */
        if ($quote->isVirtual()) {
            if ($data->hasArray(self::EXTRA_FEE_BLOCK)) {
                $data->removeArray(self::EXTRA_FEE_BLOCK);
            }
        }
    }

    /**
     * Change Agreement Block
     * @param AccessData $data
     */
    private function changeAgreementBlock(AccessData $data)
    {

        if ($data->hasArray(self::DEFAULT_AGREEMENT_BLOCK)) {
            $data->setArray(
                self::SIDEBAR_AGREEMENT_BLOCK,
                $data->getArray(self::DEFAULT_AGREEMENT_BLOCK)
            );
            $data->setArray(self::SIDEBAR_AGREEMENT_BLOCK.'.sortOrder', '4');
            if ($data->hasArray(self::AGREEMENT_BLOCK_VALIDATOR)) {
                $data->setArray(self::AGREEMENT_BLOCK_VALIDATOR, 'Magedelight_OneStepCheckout/js/view/agreement-validation');
            }
            $data->removeArray(self::DEFAULT_AGREEMENT_BLOCK);
        }
    }

    /**
     * Set Item level gift message
     * @param AccessData $data
     */
    private function setItemLevelGiftMessage(AccessData $data, $quote)
    {
        /* @var $quote \Magento\Quote\Model\Quote */
        //checkout.sidebar.summary.cart_items.details.gift_message_
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $id = $item->getItemId();
            $giftMessage = self::ITEM_LEVEL_GIFT_MESSAGE.'.gift_message_'.$id;
            //echo "<pre>"; print_r($giftMessage);
            $data->setArray($giftMessage.'.component', 'Magedelight_OneStepCheckout/js/view/gift-message/content');
            $data->setArray($giftMessage.'.config.template', 'Magedelight_OneStepCheckout/gift-message/content');
            $data->setArray($giftMessage.'.config.formTemplate', 'Magedelight_OneStepCheckout/gift-message/form');
            $data->setArray($giftMessage.'.config.itemId', $id);
            $data->setArray($giftMessage.'.config.itemName', $item->getName());
            $data->setArray($giftMessage.'.displayArea', 'gift_message_'.$id);
        }
    }

    /**
     * set the order of the all steps
     * @param AccessData $data
     */
    private function setStepSortOrder(AccessData $data, $steps)
    {
        $allSteps = [
            'shipping_adddress' => self::SHIPPING_STEP.'.sortOrder',
            'payment' => self::BILLING_STEP.'.sortOrder',
            'shipping_method' => self::SHIPPING_METHOD.'.sortOrder',
            'review' => 'components.checkout.children.steps.children.order-review.sortOrder'

        ];
        foreach ($allSteps as $key => $step) {
            $data->setArray($step, $steps['rows'][$key]['sort_order']);
        }
    }

    /**
     * set the registration block
     * @param AccessData $data
     */
    private function setRegistrationBlock(AccessData $data)
    {
        $allSteps = [
            'component' => 'Magedelight_OneStepCheckout/js/view/form/element/email',
            'requiredPasswordCharacter' => (int) $this->oscHelper->getRequiredPasswordCharacter(),
            'minimumPasswordLength' => (int) $this->oscHelper->getMinimumPasswordLength()
        ];
        foreach ($allSteps as $key => $step) {
            $data->setArray(self::CUSTOMER_EMAIL_BLOCK.'.'.$key, $step);
        }
    }

    /**
     * Change the block of the billing address
     * @param AccessData $data
     */
    private function changeBillingAddressBlock(AccessData $data)
    {
        $billingStep = self::BILLING_STEP.'.children.payment.children.afterMethods.children.billing-address-form';
        $shippingStep = self::SHIPPING_STEP.'.children.billing-address-form';
        if ($data->hasArray($billingStep)) {
            $data->setArray($shippingStep, $data->getArray($billingStep));
            $data->setArray($shippingStep.'.sortOrder', 2);
            $data->removeArray($billingStep);
        }
    }

    /**
     * Fixed issue of Amazone pay
     * @param AccessData $data
     */
    private function FixedAmazonePay(AccessData $data, $quote)
    {
        /* @var $quote \Magento\Quote\Model\Quote */
        if (!$quote->isVirtual() && $this->amazonHelper->isPwaEnabled()) {
            $shippingConfig = self::SHIPPING_STEP.'.children.shippingAddress.component';
            $paymentConfig = self::BILLING_STEP.'.children.payment.children.payments-list.component';
            $data->setArray($shippingConfig, 'Magedelight_OneStepCheckout/js/view/shipping');
            $data->setArray($paymentConfig, 'Magedelight_OneStepCheckout/js/view/payment/list');
        }
    }

    /**
     * Fixed the issue of the store pickup
     * @param AccessData $data
     */
    private function FixedStorePickup(AccessData $data)
    {
        $shippingConfig = self::SHIPPING_STEP.'.children.shippingAddress.children.shippingAdditional';
        $shippingMethodConfig = self::SHIPPING_METHOD.'.children.shippingAdditional';
        if ($data->hasArray($shippingConfig)) {
            $data->setArray($shippingMethodConfig, $data->getArray($shippingConfig));
        }
    }

    private function getDeliveryLayout()
    {
        $layout = [
            'component' => 'uiComponent',
            'displayArea' => 'mdosc-delivery-date',
            'children' => [
                'delivery_date' => [
                    'component' => 'Magedelight_OneStepCheckout/js/view/delivery-date',
                    'displayArea' => 'delivery-date-block',
                    'deps' => 'checkoutProvider',
                    'dataScopePrefix' => 'delivery_date',
                    'children' => [
                        'form-fields' => [
                            'component' => 'uiComponent',
                            'displayArea' => 'delivery-date-block',
                            'children' => [
                                'md_osc_delivery_date' => [
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'config' => [
                                        'customScope' => 'delivery_date',
                                        'template' => 'ui/form/element/hidden',
                                        'id' => 'md_osc_delivery_date'
                                    ],
                                    'dataScope' => 'delivery_date.md_osc_delivery_date',
                                    'provider' => 'checkoutProvider',
                                    'id' => 'md_osc_delivery_date'
                                ],
                                'md_osc_delivery_time' => [
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'config' => [
                                        'customScope' => 'delivery_date',
                                        'template' => 'ui/form/element/hidden',
                                        'id' => 'md_osc_delivery_time'
                                    ],
                                    'dataScope' => 'delivery_date.md_osc_delivery_time',
                                    'provider' => 'checkoutProvider',
                                    'id' => 'md_osc_delivery_time'
                                ],
                                'md_osc_delivery_comment' => [
                                    'component' => 'Magento_Ui/js/form/element/textarea',
                                    'config' => [
                                        'customScope' => 'delivery_date',
                                        'template' => 'ui/form/field',
                                        'elementTmpl' => 'ui/form/element/textarea',
                                        'options' => [],
                                        'id' => 'md_osc_delivery_comment'
                                    ],
                                    'dataScope' => 'delivery_date.md_osc_delivery_comment',
                                    'label' => '',
                                    'provider' => 'checkoutProvider',
                                    'visible' => (boolean) $this->oscHelper->isDeliveryDateComment(),
                                    'validation' => [],
                                    'sortOrder' => 20,
                                    'id' => 'md_osc_delivery_comment',
                                    'placeholder' => 'Leave your shipping comment'
                                ]
                            ],
                        ],
                    ]
                ]
            ]
        ];
        return $layout;
    }
}

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
namespace Magedelight\OneStepCheckout\Model;

class CheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \Magedelight\OneStepCheckout\Helper\Data
     */
    private $oscHelper;

    /**
     * @var DeliveryDate
     */
    protected $deliveryDateModel;

    /**
     * @var \Magento\GiftMessage\Model\GiftMessageConfigProvider
     */
    protected $giftMessageConfigProvider;

    /**
     * @param \Magedelight\OneStepCheckout\Helper\Data $oscHelper
     */
    public function __construct(
        \Magedelight\OneStepCheckout\Helper\Data $oscHelper,
        \Magedelight\OneStepCheckout\Model\DeliveryDate $deliveryDateModel,
        \Magento\GiftMessage\Model\GiftMessageConfigProvider $giftMessageConfigProvider
    ) {
        $this->oscHelper = $oscHelper;
        $this->deliveryDateModel = $deliveryDateModel;
        $this->giftMessageConfigProvider = $giftMessageConfigProvider;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        $output['suggest_address'] = (boolean) $this->oscHelper->getCheckoutAddessSugetion();
        $output['google_api_key'] = $this->oscHelper->getCheckoutGoogleApiKey();
        $output['show_discount'] = (boolean) $this->oscHelper->showDiscountCoupon();
        $output['show_comment'] = (boolean) $this->oscHelper->showComments();
        $output['show_newsletter'] = (boolean) $this->oscHelper->showNewsletter();
        $output['default_newsletter_checked'] = (boolean) $this->oscHelper->isNewsletterChecked();
        $output['edit_product'] = (boolean) $this->oscHelper->isEditProductAllowed();
        $output['billing_region_class'] = $this->getRegionClass('billing');
        $output['shipping_region_class'] = $this->getRegionClass('shipping');
        $output['billing_region_additional_class'] = (array) $this->getAdditionalClass('billing');
        $output['shipping_region_additional_class'] = (array) $this->getAdditionalClass('shipping');
        $output['agreement_block'] = $this->oscHelper->showAgreements();
        // Delivery Date Checkout Config
        $output['delivery_date_enabled'] = (boolean) $this->oscHelper->isDeliveryDateEnabled();
        $output['delivery_date_required'] = (boolean) $this->oscHelper->isDeliveryDateRequired();
        $output['delivery_date_comment'] = (boolean) $this->oscHelper->isDeliveryDateComment();
        $output['delivery_date_min_interval'] = (int) $this->oscHelper->getDeliveryMinInterval();
        $output['delivery_date_max_interval'] = (int) $this->oscHelper->getDeliveryMaxInterval();
        $output['delivery_date_timeslot'] = $this->deliveryDateModel->getDeliveryTimeSlot();
        // Gift wrapper Config Provider
        $output['mdosc_extrafee_enabled'] = (boolean) $this->oscHelper->isExtraFeeEnabled();
        $output['mdosc_extrafee'] = (float) $this->oscHelper->getExtraFee();
        $output['mdosc_extrafee_label'] = (float) $this->oscHelper->getExtraFeeLabel();
        $output['mdosc_extrafee_checkbox_label'] = $this->oscHelper->getExtraFeeCheckboxLabel();
        // Default Shipping & Payment Method
        if ($this->oscHelper->getDefaultShippingMethod()) {
            $shippingMethod = explode('-', $this->oscHelper->getDefaultShippingMethod());
            $output['mdosc_default_shipping_carrier_code'] = $shippingMethod[0];
            $output['mdosc_default_shipping_method_code'] = $shippingMethod[1];
        } else {
            $output['mdosc_default_shipping_carrier_code'] = '';
            $output['mdosc_default_shipping_method_code'] = '';
        }
        $output['mdosc_default_payment_method'] = $this->oscHelper->getDefaultPaymentMethod();
        $output['giftMessageConfig'] = $this->giftMessageConfigProvider->getConfig();

        $stepConfig = $this->oscHelper->getStepConfig();
        $output['payment_step_config_label'] = isset($stepConfig['rows']['payment']['label']) ? $stepConfig['rows']['payment']['label'] : 'Payment Method';
        $output['shipping_address_step_config_label'] = isset($stepConfig['rows']['shipping_adddress']['label']) ? $stepConfig['rows']['shipping_adddress']['label'] : 'Shipping Address';
        $output['shipping_method_step_config_label'] = isset($stepConfig['rows']['shipping_method']['label']) ? $stepConfig['rows']['shipping_method']['label'] : 'Shipping Method';
        $output['review_step_config_label'] = isset($stepConfig['rows']['review']['label']) ? $stepConfig['rows']['review']['label'] : 'Order Review';
        $output['displayBillingAfterShippingAddress'] = $this->oscHelper->getBillingAddressBlock() == 'shipping' ? (boolean) true : (boolean) false;
        $output['mdoscLayout'] = $this->oscHelper->getLayout();
        $output['mdoscBlocks'] = $this->setMappedBlocks();
        $output['mdoscRegistrationEnabled'] = (boolean) $this->oscHelper->isRegistrationEnabled();
        $output['mdoscAutoRegistrationEnabled'] = (boolean) $this->oscHelper->isAutoRegistrationEnabled();
        $output['place_order_position'] = $this->oscHelper->getPlaceOrderPosition();
        $output['save_additional_info_from_payment'] = (boolean) false;
        if ($this->oscHelper->isVersionAbove('2.3.1')) {
            $output['paypal_in_context'] = $this->oscHelper->isPayPalContext();
        } else {
            $output['paypal_in_context'] = false;
        }
        return $output;
    }

    /**
     * @return array
     */
    private function setMappedBlocks()
    {
        if ($this->oscHelper->getStepConfig()) {
            $steps = $this->oscHelper->getStepConfig();
            $block1 = [];
            if (isset($steps['rows']['shipping_method'])) {
                $block1 = [
                        'code' => 'checkout.steps.shippingMethods',
                        'sortOrder' => $steps['rows']['shipping_method']['sort_order'],
                        'label' => $steps['rows']['shipping_method']['label']
                ];
            }
            $block2 = [];
            if (isset($steps['rows']['shipping_adddress'])) {
                $block2 = [
                        'code' => 'checkout.steps.shipping-step',
                        'sortOrder' => $steps['rows']['shipping_adddress']['sort_order'],
                        'label' => $steps['rows']['shipping_adddress']['label']
                ];
            }
            $block3 = [];
            if (isset($steps['rows']['payment'])) {
                $block3 = [
                    'code' => 'checkout.steps.billing-step',
                    'sortOrder' => $steps['rows']['payment']['sort_order'],
                    'label' => $steps['rows']['payment']['label']
                ];
            }
            $block4 = [];
            if (isset($steps['rows']['review'])) {
                $block4 = [
                    'code' => 'checkout.steps.order-review',
                    'sortOrder' => $steps['rows']['review']['sort_order'],
                    'label' => $steps['rows']['review']['label']
                ];
            }
            return [$block1,$block2,$block3,$block4];
        }
    }

    /**
     * @param $code
     * @param $form
     * @return array
     */
    private function getAdditionalClass($form)
    {
        $classes = [];
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();
        if (isset($formConfig['rows']['region_id']['additional_class'])) {
            if ($formConfig['rows']['region_id']['additional_class']) {
                $classes = explode(' ', $formConfig['rows']['region_id']['additional_class']);
            }
        }
        return $classes;
    }

    private function getRegionClass($form)
    {
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();
        if (isset($formConfig['rows']['region_id']['width'])) {
            switch ($formConfig['rows']['region_id']['width']) {
                case '50':
                    return 'md-input-width-50';
                    break;
                case '100':
                    return 'md-input-width-100';
                    break;
                default:
                    return '';
                    break;
            }
        }
        return '';
    }
}

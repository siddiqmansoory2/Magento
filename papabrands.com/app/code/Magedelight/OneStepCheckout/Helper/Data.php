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

namespace Magedelight\OneStepCheckout\Helper;

use Magento\Checkout\Model\Session;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\AccountManagement;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ONESTEPCHECKOUT_ACTIVE = 'onestepcheckout/general/active';
    const XML_PATH_ONESTEPCHECKOUT_TITLE = 'onestepcheckout/general/checkout_title';
    const XML_PATH_ONESTEPCHECKOUT_META_TITLE = 'onestepcheckout/general/checkout_meta_title';
    const XML_PATH_ONESTEPCHECKOUT_DESCRIPTION = 'onestepcheckout/general/checkout_description';
    const XML_PATH_ONESTEPCHECKOUT_EDIT_PRODUCT = 'onestepcheckout/general/edit_product';
    const XML_PATH_ONESTEPCHECKOUT_SUGGEST_ADDRESS = 'onestepcheckout/general/suggest_address';
    const XML_PATH_ONESTEPCHECKOUT_GOOGLE_API_KEY = 'onestepcheckout/general/google_api_key';
    const XML_PATH_ONESTEPCHECKOUT_REDIRECT_TO_CHECKOUT = 'onestepcheckout/general/redirect_to_checkout';
    const XML_PATH_ONESTEPCHECKOUT_REGISTRATION = 'onestepcheckout/general/registration';
    const XML_PATH_ONESTEPCHECKOUT_AUTO_REGISTRATION = 'onestepcheckout/general/auto_registration';

    const XML_PATH_ONESTEPCHECKOUT_SHOW_HEADER = 'onestepcheckout/display/display_header';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_FOOTER = 'onestepcheckout/display/display_footer';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_COMMENTS = 'onestepcheckout/display/display_comments';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_NEWSLETTER = 'onestepcheckout/display/display_newsletter';
    const XML_PATH_ONESTEPCHECKOUT_DEFAULT_NEWSLETTER_CHECKED = 'onestepcheckout/display/default_newsletter_checked';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_DISCOUNT_COUPON = 'onestepcheckout/display/display_coupon';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_PRODUCT_THUMBNAIL = 'onestepcheckout/display/display_product_thumbnail';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_AGREEMENTS = 'onestepcheckout/display/display_agreements';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_TOP_CMS_BLOCK = 'onestepcheckout/display/checkout_header_block';
    const XML_PATH_ONESTEPCHECKOUT_SHOW_BOTTOM_CMS_BLOCK = 'onestepcheckout/display/checkout_footer_block';
    const XML_PATH_ONESTEPCHECKOUT_SUCCESS_TOP_CMS_BLOCK = 'onestepcheckout/display/success_header_block';
    const XML_PATH_ONESTEPCHECKOUT_SUCCESS_BOTTOM_CMS_BLOCK = 'onestepcheckout/display/success_footer_block';

    const XML_PATH_ONESTEPCHECKOUT_STYLE_HEADING_COLOR = 'onestepcheckout/display_style/heading_color';
    const XML_PATH_ONESTEPCHECKOUT_STYLE_DESCRIPTION_COLOR = 'onestepcheckout/display_style/heading_description_color';
    const XML_PATH_ONESTEPCHECKOUT_STYLE_LAYOUT_COLOR = 'onestepcheckout/display_style/steps_layout_color';
    const XML_PATH_ONESTEPCHECKOUT_STYLE_FONT_COLOR = 'onestepcheckout/display_style/steps_font_color';
    const XML_PATH_ONESTEPCHECKOUT_STYLE_ORDER_BUTTON_COLOR = 'onestepcheckout/display_style/order_button_color';

    const XML_PATH_ONESTEPCHECKOUT_SHIPPING_FIELD_CUSTOMIZATION = 'onestepcheckout/shipping_field/shipping_fields_customization';
    const XML_PATH_ONESTEPCHECKOUT_BILLING_FIELD_CUSTOMIZATION = 'onestepcheckout/billing_field/billing_fields_customization';
    const SECTION_CONFIG_ONESTEPCHECKOUT = 'onestepcheckout';
    /**
     * Step Config Provider
     */
    const XML_PATH_ONESTEPCHECKOUT_LAYOUT = 'onestepcheckout/step_config/layout';
    const XML_PATH_ONESTEPCHECKOUT_STEP_CONFIG = 'onestepcheckout/step_config/customization';
    //const XML_PATH_ONESTEPCHECKOUT_REVIEW_LABEL = 'onestepcheckout/step_config/review_title';
    const XML_PATH_ONESTEPCHECKOUT_BILLING_ADDRESS = 'onestepcheckout/step_config/billing_address';
    const XML_PATH_ONESTEPCHECKOUT_PLACE_ORDER_POSITION = 'onestepcheckout/step_config/place_order_position';
    const XML_PATH_CHECKOUT_BILLING_ADDRESS = 'checkout/options/display_billing_address_on';
    const XML_PATH_ADDRESS_LINE = 'onestepcheckout/step_config/street_lines';
    const XML_PATH_CUSTOMER_ADDRESS_LINE = 'customer/address/street_lines';
    /**
     * Delivery Date Config Provider
     */
    const XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_ENABLED = 'onestepcheckout/delivery_date/enabled';
    const XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_REQUIRED = 'onestepcheckout/delivery_date/required';
    const XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_COMMENT = 'onestepcheckout/delivery_date/comment';
    const XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_TIMESLOT = 'onestepcheckout/delivery_date/timeslot';
    const XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_MININTERVAL = 'onestepcheckout/delivery_date/min_interval';
    const XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_MAXINTERVAL = 'onestepcheckout/delivery_date/max_interval';
    /**
     * Extra Fee Config Provider
     */
    const XML_PATH_ONESTEPCHECKOUT_EXTRAFEE_ENABLED = 'onestepcheckout/extra_fee/enabled';
    const XML_PATH_ONESTEPCHECKOUT_EXTRAFEE = 'onestepcheckout/extra_fee/fee';
    const XML_PATH_ONESTEPCHECKOUT_EXTRAFEE_LABEL = 'onestepcheckout/extra_fee/fee_title';
    const XML_PATH_ONESTEPCHECKOUT_EXTRAFEE_CHECKBOX_TITLE = 'onestepcheckout/extra_fee/fee_checkbox_title';
    /**
     * Gift Message Config Provider
     */
    const XML_PATH_ONESTEPCHECKOUT_GIFTMESSAGE_ORDER = 'onestepcheckout/gift_message/order_level';
    const XML_PATH_ONESTEPCHECKOUT_GIFTMESSAGE_ITEM = 'onestepcheckout/gift_message/item_level';
    const XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS = 'sales/gift_options/allow_items';
    const XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER = 'sales/gift_options/allow_order';

    /**
     * Default Shipping And Payment Method
     */
    const XML_PATH_ONESTEPCHECKOUT_DEFAULT_SHIPPINGMETHOD = 'onestepcheckout/shipping_payment_method/shipping';
    const XML_PATH_ONESTEPCHECKOUT_DEFAULT_PAYMENTMETHOD = 'onestepcheckout/shipping_payment_method/payment';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $storeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Unserialize\Unserialize
     */
    protected $unserialize;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    protected $checkoutSession;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Unserialize\Unserialize $unserialize
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Serialize\SerializerInterface $unserialize,
        \Magento\Framework\Serialize\Serializer\Json $json,
        Session $checkoutSession
    ) {
        $this->storeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->unserialize = $unserialize;
        $this->json = $json;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
    /**
     *
     * @param string $relativePath
     * @return string
     */
    public function getOneStepConfig($relativePath)
    {
        return $this->scopeConfig->getValue(
            self::SECTION_CONFIG_ONESTEPCHECKOUT . '/' . $relativePath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    public function isModuleEnable()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutTitle()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutMetaTitle()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_META_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutDescription()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DESCRIPTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutAddessSugetion()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SUGGEST_ADDRESS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutGoogleApiKey()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_GOOGLE_API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function allowRedirectCheckoutAfterProductAddToCart()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_REDIRECT_TO_CHECKOUT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function showComments()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHOW_COMMENTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function showNewsletter()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHOW_NEWSLETTER,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isNewsletterChecked()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DEFAULT_NEWSLETTER_CHECKED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function showDiscountCoupon()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHOW_DISCOUNT_COUPON,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function showProductThumbnail()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHOW_PRODUCT_THUMBNAIL,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function showAgreements()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHOW_AGREEMENTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCmsBlockByArea($area)
    {
        if ($area == 'header') {
            return $this->storeConfig->getValue(
                self::XML_PATH_ONESTEPCHECKOUT_SHOW_TOP_CMS_BLOCK,
                ScopeInterface::SCOPE_STORE
            );
        } else {
            return $this->storeConfig->getValue(
                self::XML_PATH_ONESTEPCHECKOUT_SHOW_BOTTOM_CMS_BLOCK,
                ScopeInterface::SCOPE_STORE
            );
        }
    }

    public function getSuccessCmsBlockByArea($area)
    {
        if ($area == 'header') {
            return $this->storeConfig->getValue(
                self::XML_PATH_ONESTEPCHECKOUT_SUCCESS_TOP_CMS_BLOCK,
                ScopeInterface::SCOPE_STORE
            );
        } else {
            return $this->storeConfig->getValue(
                self::XML_PATH_ONESTEPCHECKOUT_SUCCESS_BOTTOM_CMS_BLOCK,
                ScopeInterface::SCOPE_STORE
            );
        }
    }

    public function getHeadingColor()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_STYLE_HEADING_COLOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDescriptionColor()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_STYLE_DESCRIPTION_COLOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getStepsFontColor()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_STYLE_FONT_COLOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getLayoutColor()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_STYLE_LAYOUT_COLOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getOrderButtonColor()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_STYLE_ORDER_BUTTON_COLOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getShippingAddressFieldConfig()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHIPPING_FIELD_CUSTOMIZATION,
            ScopeInterface::SCOPE_STORE
        );
        //return unserialize($value);
        return $value ? $this->unserialize->unserialize($value) : [];
    }

    public function getBillingAddressFieldConfig()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_BILLING_FIELD_CUSTOMIZATION,
            ScopeInterface::SCOPE_STORE
        );
        return $value ? $this->unserialize->unserialize($value) : [];
    }

    public function getStepConfig()
    {
        $steps = [];
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_STEP_CONFIG,
            ScopeInterface::SCOPE_STORE
        );
        if ($value) {
            $steps = $this->unserialize->unserialize($value);
            if ($this->getQuote()->isVirtual()) {
                if ($steps['rows']['payment']['sort_order'] < $steps['rows']['review']['sort_order']) {
                    $steps['rows']['payment']['sort_order'] = '0';
                    $steps['rows']['review']['sort_order'] = '1';
                } else {
                    $steps['rows']['payment']['sort_order'] = '1';
                    $steps['rows']['review']['sort_order'] = '0';
                }
            }
        }
        return $steps;
    }

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBillingAddressBlock()
    {
        if ($this->getQuote()->isVirtual()) {
            return 'payment';
        }
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_BILLING_ADDRESS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPlaceOrderPosition()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_PLACE_ORDER_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAddressLine()
    {
        $line = $this->storeConfig->getValue(
            self::XML_PATH_ADDRESS_LINE,
            ScopeInterface::SCOPE_STORE
        );
        if ($line) {
            return $line;
        }
        return $this->storeConfig->getValue(
            self::XML_PATH_CUSTOMER_ADDRESS_LINE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLayout()
    {
        if ($this->getQuote()->isVirtual()) {
            return '2column';
        }
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_LAYOUT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLayoutClass()
    {
        $layout = $this->getLayout();
        if ($layout == '3column') {
            return 'layout-3columns-osc';
        } else {
            return 'layout-2columns-osc';
        }
    }

    public function isEditProductAllowed()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_EDIT_PRODUCT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnabled()
    {
        return $this->getConfig('onestepcheckout/general/active');
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isVersionAbove($patchVersion)
    {
        if ($this->isEnabled()) {
            $version = $this->productMetadata->getVersion();
            return version_compare($version, $patchVersion, '>=');
        }
        return false;
    }

    /**
     * Delivery Date Is Enabled Or Not
     * @return bool|mixed
     */
    public function isDeliveryDateEnabled()
    {
        if ($this->isEnabled()) {
            return $this->storeConfig->getValue(
                self::XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_ENABLED,
                ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Delivery Date Is Required Or Not
     * @return bool|mixed
     */
    public function isDeliveryDateRequired()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_REQUIRED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Delivery Date Comment Box Show
     * @return mixed
     */
    public function isDeliveryDateComment()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_COMMENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Delivery Date Time Slot
     * @return mixed
     */
    public function getDeliveryTimeSlot()
    {
        $value = $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_TIMESLOT,
            ScopeInterface::SCOPE_STORE
        );
        if (empty($value)) {
            return false;
        }
        if ($this->isSerialized($value)) {
            $unserializer = $this->unserialize;
        } else {
            $unserializer = $this->json;
        }
        return $unserializer->unserialize($value);
    }

    /**
     * Check if value is a serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }

    /**
     * Get Minimum Day Interval
     * @return mixed
     */
    public function getDeliveryMinInterval()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_MININTERVAL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Maximum Day Interval
     * @return mixed
     */
    public function getDeliveryMaxInterval()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DELIVERYDATE_MAXINTERVAL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Extra Fee Is Enabled Or Not
     * @return mixed
     */
    public function isExtraFeeEnabled()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_EXTRAFEE_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Extra Fee
     * @return mixed
     */
    public function getExtraFee()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_EXTRAFEE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Extra Fee Label
     * @return mixed
     */
    public function getExtraFeeLabel()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_EXTRAFEE_LABEL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Extra Fee Checkbox Title
     * @return mixed
     */
    public function getExtraFeeCheckboxLabel()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_EXTRAFEE_CHECKBOX_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function showHeader()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHOW_HEADER,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function showFooter()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_SHOW_FOOTER,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultShippingMethod()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DEFAULT_SHIPPINGMETHOD,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultPaymentMethod()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_DEFAULT_PAYMENTMETHOD,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getGiftMessageOrderLevel()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_GIFTMESSAGE_ORDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getGiftMessageItemLevel()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_GIFTMESSAGE_ITEM,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isGiftMessageOrderLevel()
    {
        $result = false;
        $osc = $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_GIFTMESSAGE_ORDER,
            ScopeInterface::SCOPE_STORE
        );
        $core = $this->storeConfig->getValue(
            self::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
            ScopeInterface::SCOPE_STORE
        );
        if ($core) {
            $osc ? $result = true : $result = false;
        }
        return $result;
    }

    public function isGiftMessageItemLevel()
    {
        $result = false;
        $osc = $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_GIFTMESSAGE_ITEM,
            ScopeInterface::SCOPE_STORE
        );
        $core = $this->storeConfig->getValue(
            self::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            ScopeInterface::SCOPE_STORE
        );
        if ($core) {
            $osc ? $result = true : $result = false;
        }
        return $result;
    }

    public function getRequiredPasswordCharacter()
    {
        return $this->storeConfig->getValue(
            AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMinimumPasswordLength()
    {
        return $this->storeConfig->getValue(
            AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isRegistrationEnabled()
    {
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_REGISTRATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isAutoRegistrationEnabled()
    {
        if ($this->isRegistrationEnabled()) {
            return false;
        }
        return $this->storeConfig->getValue(
            self::XML_PATH_ONESTEPCHECKOUT_AUTO_REGISTRATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isPayPalContext($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'payment/paypal_express/in_context',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

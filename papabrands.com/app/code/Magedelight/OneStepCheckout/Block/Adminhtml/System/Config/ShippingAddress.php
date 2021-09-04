<?php
/**
 *
 */

namespace Magedelight\OneStepCheckout\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\AddressMetadataInterface;
use Magedelight\OneStepCheckout\Model\Address\Form\DefaultSortOrder;
use Magedelight\OneStepCheckout\Model\Address\Form\DefaultWidth;
use Magento\Directory\Model\Config\Source\Country;

class ShippingAddress extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'system/config/address.phtml';

    /**
     * @var AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var DefaultSortOrder
     */
    protected $defaultSortOrder;

    /**
     * @var DefaultWidth
     */
    protected $defaultWidth;

    /**
     * @var Country
     */
    protected $country;

    /**
     * ShippingAddress constructor.
     * @param Context $context
     * @param AddressMetadataInterface $addressMetadata
     * @param DefaultSortOrder $defaultSortOrder
     * @param DefaultWidth $defaultWidth
     * @param array $data
     */
    public function __construct(
        Context $context,
        AddressMetadataInterface $addressMetadata,
        DefaultSortOrder $defaultSortOrder,
        DefaultWidth $defaultWidth,
        Country $country,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addressMetadata = $addressMetadata;
        $this->defaultSortOrder = $defaultSortOrder;
        $this->defaultWidth = $defaultWidth;
        $this->country = $country;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->_toHtml();
    }

    /**
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAllAddressAttributes()
    {
        $allAttributes = $this->addressMetadata->getAttributes('customer_register_address');
        return $allAttributes;
    }

    /**
     * @param $code
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesFieldsFilterByCode($code)
    {
        $allAttributes = $this->getAllAddressAttributes();
        $data = $allAttributes[$code];
        return $data;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAllAddressAttributesCodes()
    {
        $allAttributes = $this->getAllAddressAttributes();
        foreach ($allAttributes as $attribute) {
            $code[] = $attribute->getAttributeCode();
        }
        return $code;
    }

    /**
     * @param $code
     * @return int|null
     */
    private function getSortOrder($code)
    {
        $configValue = $this->getElement()->getValue();
        if (isset($configValue['rows'][$code])) {
            $sortOrder = isset($configValue['rows'][$code]['sort_order']) ?
                $configValue['rows'][$code]['sort_order'] :
                $this->defaultSortOrder->getSortOrder($code);
        } else {
            $sortOrder = $this->defaultSortOrder->getSortOrder($code);
        }
        return $sortOrder;
    }

    /**
     * @param $code
     * @return int|null
     */
    private function getWidth($code)
    {
        $configValue = $this->getElement()->getValue();
        if (isset($configValue['rows'][$code])) {
            $width = isset($configValue['rows'][$code]['width']) ?
                $configValue['rows'][$code]['width'] :
                $this->defaultWidth->getDefaultWidth($code);
        } else {
            $width = $this->defaultWidth->getDefaultWidth($code);
        }
        return $width;
    }

    /**
     * @param $code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getFrontendLabel($code)
    {
        $configValue = $this->getElement()->getValue();
        if (isset($configValue['rows'][$code]['label'])) {
            $label = $configValue['rows'][$code]['label'];
        } else {
            $label = $this->getAttributesFieldsFilterByCode($code)->getFrontendLabel();
        }
        return $label;
    }

    /**
     * @param $code
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isRequired($code)
    {
        $configValue = $this->getElement()->getValue();
        if (isset($configValue['rows'][$code])) {
            $required = isset($configValue['rows'][$code]['required']) ?
                $configValue['rows'][$code]['required'] :
                0;
        } else {
            $required = $this->getAttributesFieldsFilterByCode($code)->isRequired();
        }
        return $required;
    }

    /**
     * @param $code
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isVisible($code)
    {
        $configValue = $this->getElement()->getValue();
        if (isset($configValue['rows'][$code])) {
            $required = isset($configValue['rows'][$code]['visible']) ?
                $configValue['rows'][$code]['visible'] :
                0;
        } else {
            $required = $this->getAttributesFieldsFilterByCode($code)->isVisible();
        }
        return $required;
    }

    /**
     * @param $code
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultValue($code)
    {
        $configValue = $this->getElement()->getValue();
        if (isset($configValue['rows'][$code])) {
            $required = isset($configValue['rows'][$code]['default']) ?
                $configValue['rows'][$code]['default'] :
                0;
        } else {
            $required = $this->getAttributesFieldsFilterByCode($code)->getDefaultValue();
        }
        return $required;
    }

    private function getAdditionalClass($code)
    {
        $configValue = $this->getElement()->getValue();
        if (isset($configValue['rows'][$code])) {
            $required = isset($configValue['rows'][$code]['additional_class']) ?
                $configValue['rows'][$code]['additional_class'] :
                '';
        } else {
            $required = '';
        }
        return $required;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function makeAddressFieldArray()
    {
        $fieldArray = [];
        $codes = $this->getAllAddressAttributesCodes();
        foreach ($codes as $code) {
            $this->defaultWidth->getDefaultWidth($code);
            if ($code != 'region') {
                $fieldArray[] = [
                    'code' => $code,
                    'sort_order' => $this->getSortOrder($code),
                    'label' => $this->getFrontendLabel($code),
                    'required' => $this->isRequired($code),
                    'visible' => $this->isVisible($code),
                    'width' => $this->getWidth($code),
                    'default' => $this->getDefaultValue($code),
                    'additional_class' => $this->getAdditionalClass($code),
                ];
            }
        }
        array_multisort(
            array_column($fieldArray, 'sort_order'),
            SORT_ASC,
            $fieldArray
        );

        return $fieldArray;
    }

    public function getCountryValue()
    {
        return $this->getAttributesFieldsFilterByCode('country_id')->getOptions();
        //return $this->country->toOptionArray();
    }

    public function getRegionValue()
    {
        return $this->getAttributesFieldsFilterByCode('region_id')->getOptions();
    }
}

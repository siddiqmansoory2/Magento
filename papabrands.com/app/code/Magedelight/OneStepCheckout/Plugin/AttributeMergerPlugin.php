<?php

namespace Magedelight\OneStepCheckout\Plugin;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magedelight\OneStepCheckout\Helper\Data;
use Magedelight\OneStepCheckout\Model\Address\Form\DefaultSortOrder;
use Magedelight\OneStepCheckout\Model\CountryByWebsite;

class AttributeMergerPlugin
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var DefaultSortOrder
     */
    protected $defaultSortOrder;
    protected $countrycode;

    /**
     * AttributeMergerPlugin constructor.
     * @param Data $helper
     * @param DefaultSortOrder $defaultSortOrder
     */
    public function __construct(
        Data $helper,
        DefaultSortOrder $defaultSortOrder,
        CountryByWebsite $countrycode
    ) {
        $this->helper = $helper;
        $this->defaultSortOrder = $defaultSortOrder;
        $this->countrycode = $countrycode;
    }

    public function aroundMerge(
        AttributeMerger $subject,
        \Closure $proceed,
        $elements,
        $providerName,
        $dataScopePrefix,
        array $fields = []
    ) {
        $formConfig = $this->getConfigProvider('billing');
        if ($dataScopePrefix == 'shippingAddress') {
            $formConfig = $this->getConfigProvider('shipping');
        }
        foreach ($elements as $attributeCode => &$attributeConfig) {
            if (isset($formConfig['rows'][$attributeCode]['visible'])) {
                if ($formConfig['rows'][$attributeCode]['visible']) {
                    $attributeConfig['visible'] = 1;
                } else {
                    $attributeConfig['visible'] = 0;
                }
            } else {
                $attributeConfig['visible'] = 0;
            }
        }

        $result = $proceed($elements, $providerName, $dataScopePrefix, $fields);

        foreach ($result as $key => $element) {
            $extraClasses = $this->getAdditionalClasses($key, $formConfig);
            if ($key == 'street') {
                if (!empty($extraClasses)) {
                    unset($result['street']['config']['additionalClasses']);
                    foreach ($extraClasses as $class) {
                        $result['street']['config']['additionalClasses'][$class] = true;
                    }
                }
                if (!empty($result['street']['config']['additionalClasses'])) {
                    $result['street']['config']['additionalClasses']['street'] = true;
                }
            }
            if (!empty($extraClasses)) {
                foreach ($extraClasses as $class) {
                    $result[$key]['additionalClasses'][$class] = true;
                }
            }

            $sortOrder = $this->getAdditionalSortOrder($key, $formConfig);
            if ($sortOrder) {
                $result[$key]['sortOrder'] = $sortOrder;
            }

            $label = $this->getAdditionalLabel($key, $formConfig);
            if ($label) {
                $result[$key]['label'] = $label;
            }

            $notAllowed = array('postcode','region','region_id','country_id');
            if(!in_array($key,$notAllowed)) {
                $validation = $this->getAdditionalValidation($key, $formConfig);
                if ($validation) {
                    $result[$key]['validation']['required-entry'] = true;
                } else {
                    unset($result[$key]['validation']['required-entry']);
                }
            }

            $defaultValue = $this->getDefaultValue($key, $formConfig);            
            if(!in_array($key,$notAllowed)) {
                if ($defaultValue && $defaultValue != 'selected="selected"') {
                    if ($key == 'country_id') {
                        $result['country_id']['value'] = $defaultValue;
                        $result['country_id']['initialValue'] = $defaultValue;
                        $result['country_id']['default'] = $defaultValue;
                    } else {
                        $result[$key]['value'] = $defaultValue;
                    }
                } else {

                    if ($key == 'country_id') {
                        $coreValue = $this->countrycode->getCountryByWebsite();
                        $result['country_id']['value'] = $coreValue;
                        $result['country_id']['initialValue'] = $coreValue;
                        $result['country_id']['default'] = $coreValue;
                    }
                }
            }            
        }

        return $result;
    }

    private function getConfigProvider($form)
    {
        if ($form == 'shipping') {
            return $this->helper->getShippingAddressFieldConfig();
        }

        return $this->helper->getBillingAddressFieldConfig();
    }

    private function getAdditionalClasses($code, $formConfig)
    {
        $extraClasses = [];
        if (isset($formConfig['rows'][$code]['additional_class'])) {
            if ($formConfig['rows'][$code]['additional_class']) {
                $extraClasses = explode(' ', $formConfig['rows'][$code]['additional_class']);
            }
        }
        if (isset($formConfig['rows'][$code]['width'])) {
            if ($formConfig['rows'][$code]['width']) {
                array_push($extraClasses, 'md-input-width-'.$formConfig['rows'][$code]['width']);
            }
        }
        return $extraClasses;
    }

    private function getAdditionalSortOrder($code, $formConfig)
    {
        $defaultSortOrder = $this->defaultSortOrder->getSortOrder($code);
        if (isset($formConfig['rows'][$code]['sort_order'])) {
            $defaultSortOrder = $formConfig['rows'][$code]['sort_order'] + 1;
        }
        return $defaultSortOrder;
    }

    private function getAdditionalLabel($code, $formConfig)
    {
        $label = '';
        if (isset($formConfig['rows'][$code]['label']) && $formConfig['rows'][$code]['label']) {
            $label = $formConfig['rows'][$code]['label'];
        }
        return $label;
    }

    private function getAdditionalValidation($code, $formConfig)
    {
        $required = false;
        if (isset($formConfig['rows'][$code]['required']) && $formConfig['rows'][$code]['required']) {
            $required = true;
        }
        return $required;
    }

    private function getDefaultValue($code, $formConfig)
    {
        $default = '';
        if (isset($formConfig['rows'][$code]['default']) && $formConfig['rows'][$code]['default']) {
            $default = $formConfig['rows'][$code]['default'];
        }

        return $default;
    }

    private function getVisiblity($code, $formConfig)
    {
        $visiblity = '0';
        if (isset($formConfig['rows'][$code]['visible'])) {
            if ($formConfig['rows'][$code]['visible']) {
                $visiblity = '1';
            }
        }
        return $visiblity;
    }
}

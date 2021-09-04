<?php
/* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* @category Magedelight
* @package Magedelight_OneStepCheckout
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
namespace Magedelight\OneStepCheckout\Block\Checkout;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Helper\Address as AddressHelper;
use Magedelight\OneStepCheckout\Helper\Data;
use Magedelight\OneStepCheckout\Model\Address\Form\DefaultSortOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeMerger extends \Magento\Checkout\Block\Checkout\AttributeMerger
{
    /**
     * Map form element
     *
     * @var array
     */
    protected $formElementMap = [
        'checkbox'    => 'Magento_Ui/js/form/element/select',
        'select'      => 'Magento_Ui/js/form/element/select',
        'textarea'    => 'Magento_Ui/js/form/element/textarea',
        'multiline'   => 'Magento_Ui/js/form/components/group',
        'multiselect' => 'Magento_Ui/js/form/element/multiselect',
    ];

    /**
     * Map template
     *
     * @var array
     */
    protected $templateMap = [
        'image' => 'media',
    ];

    /**
     * Map input_validation and validation rule from js
     *
     * @var array
     */
    protected $inputValidationMap = [
        'alpha' => 'validate-alpha',
        'numeric' => 'validate-number',
        'alphanumeric' => 'validate-alphanum',
        'url' => 'validate-url',
        'email' => 'email2',
    ];

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $customer;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * List of codes of countries that must be shown on the top of country list
     *
     * @var array
     */
    private $topCountryCodes;

    /**
     * @var Data
     */
    protected $oscHelper;

    /**
     * @var DefaultSortOrder
     */
    protected $defaultSortOrder;

    /**
     * @param AddressHelper $addressHelper
     * @param Session $customerSession
     * @param CustomerRepository $customerRepository
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        AddressHelper $addressHelper,
        Session $customerSession,
        CustomerRepository $customerRepository,
        DirectoryHelper $directoryHelper,
        Data $oscHelper,
        DefaultSortOrder $defaultSortOrder
    ) {
        parent::__construct(
            $addressHelper,           
            $customerSession,
            $customerRepository,
            $directoryHelper
        );
        $this->directoryHelper = $directoryHelper;
        $this->oscHelper = $oscHelper;
        $this->defaultSortOrder = $defaultSortOrder;
    }

    /**
     * Merge additional address fields for given provider
     *
     * @param array $elements
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array
     */
    public function merge($elements, $providerName, $dataScopePrefix, array $fields = [])
    {
        foreach ($elements as $attributeCode => $attributeConfig) {
            $additionalConfig = isset($fields[$attributeCode]) ? $fields[$attributeCode] : [];

            if (!$this->isFieldVisible($attributeCode, $attributeConfig, $additionalConfig) && $attributeCode == 'region') {
                continue;
            }

            if($dataScopePrefix == 'shippingAddress') {
                if (!$this->isShippingFieldVisible($attributeCode, $attributeConfig, $additionalConfig)) {
                    unset($elements[$attributeCode]);
                    unset($fields[$attributeCode]);
                    continue;
                }
            } else {
                if (!$this->isBillingFieldVisible($attributeCode, $attributeConfig, $additionalConfig)) {
                    unset($elements[$attributeCode]);
                    unset($fields[$attributeCode]);
                    continue;
                }
            }


            $fields[$attributeCode] = $this->getFieldConfig(
                $attributeCode,
                $attributeConfig,
                $additionalConfig,
                $providerName,
                $dataScopePrefix
            );
        }
        return $fields;
    }

    /**
     * Retrieve UI field configuration for given attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param array $additionalConfig field configuration provided via layout XML
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getFieldConfig(
        $attributeCode,
        array $attributeConfig,
        array $additionalConfig,
        $providerName,
        $dataScopePrefix
    ) {
        // street attribute is unique in terms of configuration, so it has its own configuration builder
        if (isset($attributeConfig['validation']['input_validation'])) {
            $validationRule = $attributeConfig['validation']['input_validation'];
            $attributeConfig['validation'][$this->inputValidationMap[$validationRule]] = true;
            unset($attributeConfig['validation']['input_validation']);
        }

        if ($attributeConfig['formElement'] == 'multiline') {
            if($dataScopePrefix == 'shippingAddress' && $attributeCode == 'street' && $this->oscHelper->isModuleEnable()) {
                $attributeConfig['sortOrder'] = $this->getAdditionalSortOrder($attributeCode,'shipping');
                $attributeConfig['label'] = $this->getAdditionalLabel($attributeCode,'shipping');
                $attributeConfig['config']['additionalClasses'] = array(
                    $this->getAdditionalClass($attributeCode,'shipping') => 1,
                    $attributeCode => 1
                );
            }
            if($dataScopePrefix != 'shippingAddress' && $attributeCode == 'street' && $this->oscHelper->isModuleEnable()) {
                $attributeConfig['sortOrder'] = $this->getAdditionalSortOrder($attributeCode,'billing');
                $attributeConfig['label'] = $this->getAdditionalLabel($attributeCode,'billing');
                $attributeConfig['config']['additionalClasses'] = array(
                    $this->getAdditionalClass($attributeCode,'shipping') => 1,
                    $attributeCode => 1
                );
            }
            return $this->getMultilineFieldConfig($attributeCode, $attributeConfig, $providerName, $dataScopePrefix);
        }

        $uiComponent = isset($this->formElementMap[$attributeConfig['formElement']])
            ? $this->formElementMap[$attributeConfig['formElement']]
            : 'Magento_Ui/js/form/element/abstract';
        $elementTemplate = isset($this->templateMap[$attributeConfig['formElement']])
            ? 'ui/form/element/' . $this->templateMap[$attributeConfig['formElement']]
            : 'ui/form/element/' . $attributeConfig['formElement'];

        if($dataScopePrefix == 'shippingAddress' && $this->oscHelper->isModuleEnable()) {
            $additionalConfig['sortOrder'] = $this->getAdditionalSortOrder($attributeCode,'shipping');
            $additionalConfig['label'] = $this->getAdditionalLabel($attributeCode,'shipping');
            $additionalConfig['additionalClasses'] = array(
                $this->getAdditionalClass($attributeCode,'shipping') => 1,
                $attributeCode => 1
            );
            $additionalConfig['validation']['required-entry'] = $this->getAdditionalValidation($attributeCode,'shipping');
            $additionalConfig['required'] = $this->getAdditionalValidation($attributeCode,'shipping');
            $additionalConfig['default'] = $this->getAdditionalDefaultValue($attributeCode, 'shipping');
        }

        if($dataScopePrefix != 'shippingAddress' && $this->oscHelper->isModuleEnable()) {
            $additionalConfig['sortOrder'] = $this->getAdditionalSortOrder($attributeCode,'billing');
            $additionalConfig['label'] = $this->getAdditionalLabel($attributeCode,'billing');
            $additionalConfig['validation']['required-entry'] = $this->getAdditionalValidation($attributeCode,'billing');
            $additionalConfig['required'] = $this->getAdditionalValidation($attributeCode,'billing');
            $additionalConfig['additionalClasses'] = array(
                $this->getAdditionalClass($attributeCode,'billing') => 1,
                $attributeCode => 1
            );
            $additionalConfig['default'] = $this->getAdditionalDefaultValue($attributeCode, 'billing');
        }

        $element = [
            'component' => isset($additionalConfig['component']) ? $additionalConfig['component'] : $uiComponent,
            'config' => [

                // customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => $dataScopePrefix,
                'customEntry' => isset($additionalConfig['config']['customEntry'])
                    ? $additionalConfig['config']['customEntry']
                    : null,
                'template' => 'ui/form/field',
                'elementTmpl' => isset($additionalConfig['config']['elementTmpl'])
                    ? $additionalConfig['config']['elementTmpl']
                    : $elementTemplate,
                'tooltip' => isset($additionalConfig['config']['tooltip'])
                    ? $additionalConfig['config']['tooltip']
                    : null
            ],
            'dataScope' => $dataScopePrefix . '.' . $attributeCode,
            'label' => isset($additionalConfig['label']) ? $additionalConfig['label'] : $attributeConfig['label'],
            'provider' => $providerName,
            'sortOrder' => isset($additionalConfig['sortOrder'])
                ? $additionalConfig['sortOrder']
                : $attributeConfig['sortOrder'],
            'validation' => $this->mergeConfigurationNode('validation', $additionalConfig, $attributeConfig),
            'options' => $this->getFieldOptions($attributeCode, $attributeConfig),
            'filterBy' => isset($additionalConfig['filterBy']) ? $additionalConfig['filterBy'] : null,
            'customEntry' => isset($additionalConfig['customEntry']) ? $additionalConfig['customEntry'] : null,
            'visible' => isset($additionalConfig['visible']) ? $additionalConfig['visible'] : true,
            'additionalClasses' => isset($additionalConfig['additionalClasses']) ? $additionalConfig['additionalClasses'] : $attributeCode . '-field',
        ];

        if($attributeCode === 'region_id') {
            $element['afterRender'] = 'afterRegionRender';
            }

        if ($attributeCode === 'region_id' || $attributeCode === 'country_id') {
            unset($element['options']);
            $element['deps'] = [$providerName];
            $element['imports'] = [
                'initialOptions' => 'index = ' . $providerName . ':dictionaries.' . $attributeCode,
                'setOptions' => 'index = ' . $providerName . ':dictionaries.' . $attributeCode
            ];
        }

        if (isset($attributeConfig['value']) && $attributeConfig['value'] != null) {
            $element['value'] = $attributeConfig['value'];
        } elseif (isset($attributeConfig['default']) && $attributeConfig['default'] != null) {
            $element['value'] = $attributeConfig['default'];
        } else {
            $defaultValue = $this->getDefaultValue($attributeCode);
            if (null !== $defaultValue) {
                $element['value'] = $defaultValue;
            }
        }

        if (isset($additionalConfig['default']) && $additionalConfig['default'] != null) {
            $element['value'] = $additionalConfig['default'];
        }
        return $element;
    }

    /**
     * Retrieve field configuration for street address attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @return array
     */
    protected function getMultilineFieldConfig($attributeCode, array $attributeConfig, $providerName, $dataScopePrefix)
    {
        $lines = [];
        unset($attributeConfig['validation']['required-entry']);
        for ($lineIndex = 0; $lineIndex < (int)$attributeConfig['size']; $lineIndex++) {
            $isFirstLine = $lineIndex === 0;
            $line = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    // customScope is used to group elements within a single form e.g. they can be validated separately
                    'customScope' => $dataScopePrefix,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'dataScope' => $lineIndex,
                'provider' => $providerName,
                'validation' => $isFirstLine
                    ? array_merge(
                        ['required-entry' => (bool)$attributeConfig['required']],
                        $attributeConfig['validation']
                    )
                    : $attributeConfig['validation'],
                'additionalClasses' => $isFirstLine ? 'field' : 'additional'

            ];
            if ($isFirstLine && isset($attributeConfig['default']) && $attributeConfig['default'] != null) {
                $line['value'] = $attributeConfig['default'];
            }
            $lines[] = $line;
        }
        return [
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => $attributeConfig['label'],
            'required' => (bool)$attributeConfig['required'],
            'dataScope' => $dataScopePrefix . '.' . $attributeCode,
            'provider' => $providerName,
            'sortOrder' => $attributeConfig['sortOrder'],
            'type' => 'group',
            'config' => [
                'template' => 'ui/group/group',
                'additionalClasses' => isset($attributeConfig['config']['additionalClasses']) ? $attributeConfig['config']['additionalClasses'] : $attributeCode
            ],
            'children' => $lines,
        ];
    }

    /**
     * @param $code
     * @param $form
     * @return mixed
     */
    private function getAdditionalSortOrder($code,$form)
    {
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();

        if (isset($formConfig['rows'][$code]['sort_order'])) {
            return $formConfig['rows'][$code]['sort_order'];
        } else {
            $defaultSortOrder = $this->defaultSortOrder->getSortOrder($code);
            if($defaultSortOrder) {
                return $defaultSortOrder;
            }
        }
    }

    /**
     * @param $code
     * @param $form
     * @return string
     */
    private function getAdditionalClass($code,$form)
    {
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();
        if (isset($formConfig['rows'][$code]['width'])) {
            switch ($formConfig['rows'][$code]['width']) {
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
    }

    /**
     * @param $code
     * @param $form
     * @return mixed
     */
    private function getAdditionalLabel($code,$form)
    {
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();

        if (isset($formConfig['rows'][$code]['label']) &&
            $formConfig['rows'][$code]['label']
        ) {
            return $formConfig['rows'][$code]['label'];
        }
    }

    /**
     * @param $code
     * @param $form
     * @return bool
     */
    private function getAdditionalValidation($code,$form)
    {
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();

        if (isset($formConfig['rows'][$code]['required'])) {
            return $formConfig['rows'][$code]['required'] ? true : false;
        } else {
            return false;
        }
    }

    protected function isShippingFieldVisible($attributeCode, array $attributeConfig, array $additionalConfig = [])
    {
        if($this->getAdditionalVisiblity($attributeCode,'shipping')) {
            $attributeConfig['visible'] = true;
            $additionalConfig['visible'] = true;
            if($attributeCode == 'vat_id') {
                return true;
            }
        } else {
            $attributeConfig['visible'] = false;
            $additionalConfig['visible'] = false;
            if($attributeCode == 'vat_id') {
                return false;
            }
        }

        return $this->isFieldVisible($attributeCode, $attributeConfig, $additionalConfig);
    }

    protected function isBillingFieldVisible($attributeCode, array $attributeConfig, array $additionalConfig = [])
    {
        if($this->getAdditionalVisiblity($attributeCode,'billing')) {
            $attributeConfig['visible'] = true;
            $additionalConfig['visible'] = true;
            if($attributeCode == 'vat_id') {
                return true;
            }
        } else {
            $attributeConfig['visible'] = false;
            $additionalConfig['visible'] = false;
            if($attributeCode == 'vat_id') {
                return false;
            }
        }
        return $this->isFieldVisible($attributeCode, $attributeConfig, $additionalConfig);
    }

    private function getAdditionalVisiblity($code,$form)
    {
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();

        if (isset($formConfig['rows'][$code]['visible']) &&
            $formConfig['rows'][$code]['visible']
        ) {
            return true;
        }
        return false;
    }

    private function getAdditionalDefaultValue($code,$form)
    {
        $form == 'shipping' ?
            $formConfig = $this->oscHelper->getShippingAddressFieldConfig() :
            $formConfig = $this->oscHelper->getBillingAddressFieldConfig();

        if (isset($formConfig['rows'][$code]['default']) &&
            $formConfig['rows'][$code]['default']
        ) {
            return $formConfig['rows'][$code]['default'];
        }
        return '';
    }
}

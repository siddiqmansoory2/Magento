<?php
/**
 *
 */

namespace Magedelight\OneStepCheckout\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class StepConfig extends Field
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'system/config/step.phtml';

    /**
     * @var array
     */
    protected $steps = [
         [
            'code' => 'shipping_adddress',
            'sort' => '0',
            'label' => 'Shipping Address'
         ],
         [
            'code' => 'shipping_method',
            'sort' => '1',
            'label' => 'Shipping Method'
         ],
         [
            'code' => 'payment',
            'sort' => '2',
            'label' => 'Payment Method'
         ],
         [
            'code' => 'review',
            'sort' => '3',
            'label' => 'Order Review'
         ]
    ];

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->_toHtml();
    }

    public function getAllSteps()
    {
        $fieldArray = [];
        $steps = $this->steps;
        foreach ($steps as $step) {
            $fieldArray[] = [
                'code' => $step['code'],
                'sort' => $this->getSortOrder($step['code']) != '' ? $this->getSortOrder($step['code']) : $step['sort'],
                'label' => $this->getLabel($step['code']) ? $this->getLabel($step['code']) : $step['label']
            ];
        }
        array_multisort(
            array_column($fieldArray, 'sort'),
            SORT_ASC,
            $fieldArray
        );

        return $fieldArray;
    }

    /**
     * @param $code
     * @return int|null
     */
    private function getSortOrder($code)
    {
        $configValue = $this->getElement()->getValue();
        $sortOrder = '';
        if (isset($configValue['rows'][$code])) {
            if (isset($configValue['rows'][$code]['sort_order'])) {
                $sortOrder = $configValue['rows'][$code]['sort_order'];
            }
        }
        return $sortOrder;
    }

    private function getLabel($code)
    {
        $configValue = $this->getElement()->getValue();
        $label = '';
        if (isset($configValue['rows'][$code])) {
            if (isset($configValue['rows'][$code]['label'])) {
                $label = $configValue['rows'][$code]['label'];
            }
        }
        return $label;
    }

    public function getDefaultLabel($code)
    {
        $steps = $this->steps;
        $label = '';
        foreach ($steps as $step) {
            if ($step['code'] == $code) {
                $label =  $step['label'];
            }
        }
        return $label;
    }

    public function getInputName($code)
    {
        $htmlName = $this->getElement()->getName();
        return $htmlName.'[rows]['.$code.']';
    }
}

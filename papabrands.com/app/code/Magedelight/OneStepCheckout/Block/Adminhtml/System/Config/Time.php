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

namespace Magedelight\OneStepCheckout\Block\Adminhtml\System\Config;

use Magedelight\OneStepCheckout\Model\Config\Source\TimeOptions;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Time
 * @package Magedelight\OneStepCheckout\Block\Adminhtml\System\Config
 */
class Time extends Select
{
    /**
     * @var TimeOptions
     */
    private $timeOptions;

    /**
     * Days constructor.
     * @param Context $context
     * @param TimeOptions $timeOptions
     * @param array $data
     */
    public function __construct(
        Context $context,
        TimeOptions $timeOptions,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->timeOptions = $timeOptions;
    }

    /**
     * @return string
     *
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions(
                $this->timeOptions->toOptionArray()
            );
        }
        return parent::_toHtml();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}

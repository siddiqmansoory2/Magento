<?php

namespace Magedelight\OneStepCheckout\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magedelight\OneStepCheckout\Helper\Data;

/**
 * Class Head
 */
class Head extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Head constructor.
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getLoader()
    {
        $path = 'Magedelight_OneStepCheckout/images/';
        $image = 'loader-1.gif';
        return $this->getViewFileUrl($path.$image);
    }

    /**
     * @return string
     */
    public function getBlockLoader()
    {
        $path = 'Magedelight_OneStepCheckout/images/';
        $image = 'block-loader-1.gif';
        return $this->getViewFileUrl($path.$image);
    }

    /**
     * @param $name
     * @return mixed|string
     */
    public function getConfigColor($name)
    {
        switch ($name) {
            case 'heading':
                $color = $this->helper->getHeadingColor();
                break;
            case 'description':
                $color = $this->helper->getDescriptionColor();
                break;
            case 'step':
                $color = $this->helper->getStepsFontColor();
                break;
            case 'layout':
                $color = $this->helper->getLayoutColor();
                break;
            case 'orderButton':
                $color = $this->helper->getOrderButtonColor();
                break;
            default:
                $color = '#000000';
                break;
        }
        return $color;
    }
}

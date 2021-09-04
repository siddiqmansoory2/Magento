<?php

namespace Meetanshi\MaintenancePage\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Url;
use Meetanshi\MaintenancePage\Helper\Data;

class Button extends Field
{
    private $urlHelper;

    private $helper;

    const CHECK_TEMPLATE = 'system/config/button.phtml';

    public function __construct(
        Url $urlHelper,
        Data $helper,
        Context $context,
        $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::CHECK_TEMPLATE);
        }
        return $this;
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'addbutton_button',
                'html_id' => $element->getHtmlId(),
                'button_label' => __('Preview'),
                'redirect_url' => $this->getPreviewUrl(),
            ]
        );

        return $this->_toHtml();
    }

    protected function getPreviewUrl()
    {
        return $this->urlHelper->getUrl($this->helper::REDIRECT_TO_PAGE_DEFAULT);
    }
}

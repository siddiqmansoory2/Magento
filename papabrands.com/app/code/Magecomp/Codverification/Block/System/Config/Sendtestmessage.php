<?php
namespace Magecomp\Codverification\Block\System\Config;

class Sendtestmessage extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Magecomp_Codverification::system/config/sendtestmessage.phtml';

	public function __construct(\Magento\Backend\Block\Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('codverification/send/testmessage',['_secure' => true]);
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
            [
                'id' => 'sendtestsms',
                'label' => __('Send Testing Message'),
            ]
        );

        return $button->toHtml();
    }

}
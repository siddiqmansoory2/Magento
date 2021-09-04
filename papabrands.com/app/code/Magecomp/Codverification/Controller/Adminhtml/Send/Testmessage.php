<?php
namespace Magecomp\Codverification\Controller\Adminhtml\Send;

class Testmessage extends \Magento\Backend\App\Action
{
    protected $helperapi;
    protected $helperdata;

    public function __construct(\Magento\Backend\App\Action\Context $context,
                                \Magecomp\Codverification\Helper\Apicall $helperapi,
                                \Magecomp\Codverification\Helper\Data $helperdata)
    {
        $this->helperapi = $helperapi;
        $this->helperdata = $helperdata;
        parent::__construct($context);
    }

    public function execute()
    {
        try
        {
            if(!$this->helperdata->isTestEnabled())
                return;

            $adminnumber = $this->helperdata->getTestMobile();
            $message = $this->helperdata->getTestMessage();

            if($adminnumber != '' && $adminnumber != null && $message != '' && $message != null)
            {
                $result = $this->helperapi->callApiUrl($adminnumber,$message);
                if($result === true)
                {
                    $this->messageManager->addSuccessMessage(__('Test Message Send Successfully.'));
                    return;
                }
                $this->messageManager->addErrorMessage($result);
                return;
            }
            $this->messageManager->addErrorMessage('Both, Testing Mobile Number and Message Are Required. ');
            return;
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return;
        }

    }
}

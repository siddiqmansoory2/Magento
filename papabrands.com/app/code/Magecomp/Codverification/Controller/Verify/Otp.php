<?php
namespace Magecomp\Codverification\Controller\Verify;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as Jsonhelper;

class Otp extends \Magento\Framework\App\Action\Action
{
    protected $helperdata;
    protected $resultJson;
    protected $checkoutSession;
    protected $jsonhelper;

    public function __construct(Context $context,
                                \Magecomp\Codverification\Helper\Data $helperdata,
                                JsonFactory $resultJson,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                Jsonhelper $jsonhelper)
    {

        $this->helperdata = $helperdata;
        $this->resultJson = $resultJson;
        $this->checkoutSession = $checkoutSession;
        $this->jsonhelper = $jsonhelper;
        parent::__construct($context);
    }

    public function execute()
    {
        try
        {
            $resultJson = $this->resultJson->create();

            if(!$this->helperdata->isEnabled())
            {
                $response = [
                    'errors' => true,
                    'message' => __("Cash On Delivery Verification is Disabled.")
                ];
                return $resultJson->setData($response);
            }

            $postdata = $this->jsonhelper->jsonDecode($this->getRequest()->getContent());
            $otp = $postdata['codcode'];
            $quoteotp = $this->checkoutSession->getQuote()->getOtp();

            if($otp != $quoteotp)
            {
                $response = [
                    'errors' => true,
                    'message' => __("Invalid OTP.")
                ];
                return $resultJson->setData($response);
            }
            else
            {
                $quote = $this->checkoutSession->getQuote();
                $quote->setCodverification(1);
                $quote->save();

                $response = [
                    'errors' => false,
                    'message' => __("Cash On Delivery Verification is Completed.")
                ];
                return $resultJson->setData($response);
            }
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
            return $resultJson->setData($response);
        }
    }
}

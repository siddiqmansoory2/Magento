<?php
namespace Papa\CodTwoFactor\Controller\Verify;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as Jsonhelper;

class Otp extends \Magecomp\Codverification\Controller\Verify\Otp
{
    protected $helperdata;
    protected $resultJson;
    protected $checkoutSession;
    protected $jsonhelper;

    public function __construct(
        Context $context,
        \Magecomp\Codverification\Helper\Data $helperdata,
        \Papa\CodTwoFactor\Helper\Apicall $apihelper,
        JsonFactory $resultJson,
        \Magento\Checkout\Model\Session $checkoutSession,
        Jsonhelper $jsonhelper
    )
    {
        $this->apihelper = $apihelper;
        parent::__construct($context, $helperdata, $resultJson, $checkoutSession, $jsonhelper);
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
            $quoteotp = $this->checkoutSession->getQuote()->getOtpToken();

            $responce = $this->apihelper->verifyOtpApi($quoteotp, $otp);

            if($responce['errors'] === "false")
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

            $response = [
                'errors' => true,
                'message' => $responce['message']
            ];
            return $resultJson->setData($response);

        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
            return $resultJson->setData($response);
        }
    }
}
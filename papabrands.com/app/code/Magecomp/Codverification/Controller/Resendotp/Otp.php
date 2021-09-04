<?php
namespace Magecomp\Codverification\Controller\Resendotp;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Otp extends \Magento\Framework\App\Action\Action
{
    protected $helperapi;
    protected $helperdata;
    protected $resultJson;
    protected $checkoutSession;
    protected $emailfilter;

    public function __construct(Context $context,
                                \Magecomp\Codverification\Helper\Apicall $helperapi,
                                \Magecomp\Codverification\Helper\Data $helperdata,
                                JsonFactory $resultJson,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Email\Model\Template\Filter $filter)
    {
        $this->helperapi = $helperapi;
        $this->helperdata = $helperdata;
        $this->resultJson = $resultJson;
        $this->checkoutSession = $checkoutSession;
        $this->emailfilter = $filter;
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

            $mobilenumber = $this->checkoutSession->getQuote()->getBillingAddress()->getTelephone();
            if($mobilenumber == '' || $mobilenumber == null)
            {
                $response = [
                    'errors' => true,
                    'message' => __("Please, Add Your Billing Address Telephone Number First.")
                ];
                return $resultJson->setData($response);
            }

            $otp = $this->checkoutSession->getQuote()->getOtp();
            $this->emailfilter->setVariables(['otp' => $otp]);
            $message = $this->helperdata->getResendOtpTemplate();
            $finalmessage = $this->emailfilter->filter($message);

            $responce = $this->helperapi->callApiUrl($mobilenumber,$finalmessage);
            if($responce === true)
            {
                $response = [
                    'errors' => false,
                    'message' => __("OTP is Successfully Resend.")
                ];
                return $resultJson->setData($response);
            }

            $response = [
                'errors' => true,
                'message' => $responce
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

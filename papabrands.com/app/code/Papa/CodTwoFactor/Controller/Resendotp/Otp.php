<?php
namespace Papa\CodTwoFactor\Controller\Resendotp;

class Otp extends \Magecomp\Codverification\Controller\Resendotp\Otp
{
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

            $otp = $this->checkoutSession->getQuote()->getOtpToken();
            $this->emailfilter->setVariables(['otp' => $otp]);
            $message = $this->helperdata->getResendOtpTemplate();
            $finalmessage = $this->emailfilter->filter($message);

            $responce = $this->helperapi->callApiUrl($mobilenumber,$finalmessage);

            if($responce['errors'] === "false")
            {
                $quote = $this->checkoutSession->getQuote();
                $quote->setOtpToken($responce['message']);
                $quote->save();

                $response = [
                    'errors' => false,
                    'message' => __("OTP is Successfully Resend.")
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
<?php
namespace Papa\CodTwoFactor\Controller\Sendotp;

class Otp extends \Magecomp\Codverification\Controller\Sendotp\Otp
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

            $otp = $this->helperdata->getOtp();
            $this->emailfilter->setVariables(['otp' => $otp]);
            $message = $this->helperdata->getOtpTemplate();
            
            $finalmessage = $this->emailfilter->filter($message);

            $responce = $this->helperapi->callApiUrl($mobilenumber,$otp);

            if($responce['errors'] === "false")
            {
                $quote = $this->checkoutSession->getQuote();
                $quote->setOtpToken($responce['message']);
                $quote->save();

                $response = [
                    'errors' => false,
                    'message' => __("OTP Send Successfully.")
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
<?php 
namespace Magecomp\Codverification\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const COD_GENERAL_ENABLED = 'codverification/general/enable';
    const COD_GENERAL_TITLE = 'codverification/general/customlabel';
    const COD_OTP_TEMPLATE = 'codverification/general/otptemplate';
    const COD_RESENDOTP_TEMPLATE = 'codverification/general/resendotptemplate';

    const COD_TEST_ENABLED = 'codverification/apitest/enable';
    const COD_TEST_MOBILE = 'codverification/apitest/testmobile';
    const COD_TEST_MESSAGE = 'codverification/apitest/testmessage';

    const COD_OTP_TYPE = 'codverification/smsgatways/otptype';
    const COD_OTP_LENGTH = 'codverification/smsgatways/otplength';

	public function __construct(\Magento\Framework\App\Helper\Context $context)
	{
		parent::__construct($context);
	}

	public function isEnabled()
	{
        return $this->scopeConfig->getValue(self::COD_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE);
    }

    public function getCustomTitle()
    {
        return $this->scopeConfig->getValue(self::COD_GENERAL_TITLE,
            ScopeInterface::SCOPE_STORE);
    }

    public function isTestEnabled()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue(self::COD_TEST_ENABLED,
            ScopeInterface::SCOPE_STORE);
    }

    public function getTestMobile()
    {
        return $this->scopeConfig->getValue(self::COD_TEST_MOBILE,
                ScopeInterface::SCOPE_STORE);
    }

    public function getTestMessage()
    {
        return $this->scopeConfig->getValue(self::COD_TEST_MESSAGE,
                ScopeInterface::SCOPE_STORE);
    }

    public function getOtpType()
    {
        return $this->scopeConfig->getValue(self::COD_OTP_TYPE,
            ScopeInterface::SCOPE_STORE);
    }

    public function getOtpLength(){
        return $this->scopeConfig->getValue(self::COD_OTP_LENGTH,
            ScopeInterface::SCOPE_STORE);
    }

    public function getOtp()
    {
        if($this->getOtpType())
        {
            return substr(str_shuffle("0123456789"), 0, $this->getOtpLength());
        }
        else
        {
            return $randomString  = substr(str_shuffle("0123456789"), 0, $this->getOtpLength());
        }
    }

    public function getOtpTemplate()
    {
        return $this->scopeConfig->getValue(self::COD_OTP_TEMPLATE,
                ScopeInterface::SCOPE_STORE);
    }

    public function getResendOtpTemplate()
    {
        return $this->scopeConfig->getValue(self::COD_RESENDOTP_TEMPLATE,
            ScopeInterface::SCOPE_STORE);
    }
}
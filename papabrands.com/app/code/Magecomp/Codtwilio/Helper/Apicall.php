<?php 
namespace Magecomp\Codtwilio\Helper;

class Apicall extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_TWILIOSMS_ACCOUNTSID = 'codverification/smsgatways/twiliosid';
	const XML_TWILIOSMS_AUTHTOKEN = 'codverification/smsgatways/twiliotoken';
	const XML_TWILIOSMS_MOBILENUMBER = 'codverification/smsgatways/twilionumber';

    protected $_logger;

	public function __construct(\Magento\Framework\App\Helper\Context $context)
	{
        $this->_logger = $context->getLogger();
		parent::__construct($context);
	}

    public function getTitle() {
        return __("Twilio");
    }

	public function getAccountsid()	{
		return $this->scopeConfig->getValue(
            self::XML_TWILIOSMS_ACCOUNTSID,
			 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}

	public function getAuthtoken(){
		return $this->scopeConfig->getValue(
            self::XML_TWILIOSMS_AUTHTOKEN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getMobileNumber(){
		return '+'.$this->scopeConfig->getValue(
            self::XML_TWILIOSMS_MOBILENUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}

    public function validateSmsConfig()
    {
        $twilioclassExist = class_exists('Twilio\Rest\Client');

        if(!$twilioclassExist) {
            $this->_logger->error(__("Run 'composer require twilio/sdk' from CLI to use Twilio."));
        }

        return $twilioclassExist && $this->getAccountsid() && $this->getAuthtoken() && $this->getMobileNumber();
    }

    public function callApiUrl($mobilenumbers,$message)
    {
        try
        {
            $account_sid = $this->getAccountsid();
            $auth_token = $this->getAuthtoken();

            if (substr($mobilenumbers, 0, 1) !== '+') {
                $mobilenumbers = '+'.$mobilenumbers;
            }

            $client = new \Twilio\Rest\Client($account_sid, $auth_token);
            $returntwilio = $client->messages->create(
                $mobilenumbers,
                array('from' => $this->getMobileNumber(),'body' => $message)
            );

            if($returntwilio->status == 'undelivered')
            {
                return false;
            }
            return true;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
<?php 
namespace Magecomp\Codverification\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

class Apicall extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_TWOFACTOR_API_KEY = 'codverification/smsgatways/twofactorapikey';
    const XML_TWOFACTOR_API_URL = 'codverification/smsgatways/twofactorendpoint';

    protected $smsgatewaylist;

	public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                array $smsgatewaylist = [])
	{
        $this->smsgatewaylist = $smsgatewaylist;
		parent::__construct($context);
	}
    public function getSmsgatewaylist()
    {
        return $this->smsgatewaylist;
    }
    public function getApiKey(){
        return $this->scopeConfig->getValue(
            self::XML_TWOFACTOR_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiUrl() {
        return $this->scopeConfig->getValue(
            self::XML_TWOFACTOR_API_URL,
             \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSelectedGatewayModel()
    {
        if($this->getSelectedGateway() != '' || $this->getSelectedGateway() != null)
        {
            $Selectedgateway = $this->smsgatewaylist[$this->getSelectedGateway()];
            return ObjectManager::getInstance()->create($Selectedgateway);
        }
        else
            return null;
    }
	
	public function callApiUrl($mobilenumbers,$otp)
    {
        try
        {
            $url = $this->getApiUrl() . $this->getApiKey() . "/SMS/+91" . $mobilenumbers . "/" . $otp;

            $response = $this->otpApiCall($url);
            return $response;
        }
        catch (\Exception $e) {

            $response = [
                'errors' => "true",
                'message' => $e->getMessage()
            ];
            return $response;
        }
    }

    public function otpApiCall($url){
        try
        {
            $ch = curl_init();
            if (!$ch){
                $response = [
                    'errors' => true,
                    'message' => "Couldn't initialize a cURL handle"
                ]; 
                return $response;
            }
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $curlresponse = curl_exec($ch); // execute
            $curlresponse = json_decode($curlresponse, true); // execute
            $curl_info = curl_getinfo($ch);

            if ($curlresponse == FALSE)
            {
                $response = [
                    'errors' => "true",
                    'message' => "cURL error: ".curl_error($ch)
                ];
                return $response;
            }
            elseif($curlresponse['Status'] == "Error")
            {
                $response = [
                    'errors' => "true",
                    'message' => "Error: ".$curlresponse['Details']
                ];
                return $response;
            }
            elseif($curl_info['http_code'] != 200)
            {
                $response = [
                    'errors' => "true",
                    'message' => "Error: non-200 HTTP status code: ".$curl_info['http_code']
                ];
                return $response;
            }
            else
            {
                if($curlresponse['Status'] == 'Success')
                {
                    $response = [
                        'errors' => "false",
                        'message' => $curlresponse['Details']
                    ];

                    return $response;
                }

                $response = [
                    'errors' => "true",
                    'message' => "Error sending: status code [".$api_result[0]."] description [".$api_result[1]."]"
                ];
                return $response;
            }
            curl_close($ch);
        } 
        catch (\Exception $e) {
            $response = [
                'errors' => "true",
                'message' => $e->getMessage()
            ];
            return $response;
        }
    }
}
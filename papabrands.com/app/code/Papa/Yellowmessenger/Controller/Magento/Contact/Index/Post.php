<?php

namespace Papa\Yellowmessenger\Controller\Magento\Contact\Index;


class Post extends \Magento\Contact\Controller\Index\Post
{
	/**
     * Index action
     *
     * @return $this
     */
    public function execute($coreRoute = null)
    {
        $param = $this->getRequest()->getParams();		
		/*print_r($param);*/		
		$email_html="<body class='body'><table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td align='center' valign='top'><table width='700' border='0' cellspacing='0' cellpadding='0'><tr><td style='background: #11304c; padding: 30px 20px;'><img src='https://runtime-solutions.com/papa-brand/images/papa-logo.png' alt='logo' style='margin: auto; display: block; max-width: 70%;'></td></tr><tr><td style='border: 2px solid #acad94; padding: 20px; width: 100%;'><table width='100%' border='0' cellspacing='0' cellpadding='0' style='border: 2px solid #acad94'><tr><td style='padding: 15px 30px; width: 100%;'><table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 10px;'> Hi ".$param['name'].".,</p></td></tr><tr><td style='width: 100%;'><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 10px; margin-top: 5px;'> Thank you for your interest in PAPA. We’re glad to help you out.</p></td></tr><tr><td style='width: 100%'><p style='font-size: 1.1rem; font-weight: 600;'> Our customer service executive will be contacting you within the next 24- 48 hours to resolve your query. </p></td></tr><tr><td><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 20px;'> Thank you for contacting PAPA. </p></td></tr><tr><td><p style='font-size: 1.1rem; font-weight: 600;'> Regards, <br> Papa Brands. </p></td></tr><tr><td><p style='font-size: 1.1rem; font-weight: 600; text-align: center; line-height: 25px; margin-bottom: 20px;'> If you have any queries, contact us at: support@papabrands.com or call/whatsapp us at: +91 9372221906</p></td></tr></table></td></tr></table></td></tr></table></td></tr></table>";				
		/*die;*/		
		/*$this->messageManager->addSuccess('Thank you for contacting us');	*/			
		/**/$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://app.yellowmessenger.com/api/engagements/notifications/v2/push?bot=x1627030069843',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => false,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'{
				"userDetails": {
				"email": "'.$param['email'].'"
			},
			"notification": {
				"type": "email",
				"subject": "Thank you for contacting us",
				"sender": "support@papabrands.com",
				"freeTextContent": "'.$email_html.'"
			}
			}',
			CURLOPT_HTTPHEADER => array(
				'x-auth-token: 5deabcd62f4191d541850fae2d6633188e208d5d8b7a1f7a11d898da73d169ae',
				'Content-Type: application/json'
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);/**/			
        return parent::execute($coreRoute);
    }
}
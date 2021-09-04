<?php

namespace PayUIndia\Payu\Session;

use Magento\Framework\Session\Config as DefaultConfig;

class CustomConfig extends DefaultConfig
{
    public function setCookiePath($path, $default = null)
    {   
        parent::setCookiePath($path, $default);
		/*
        $path = $this->getCookiePath();

        //check and update path of cookie
        if (!preg_match('/SameSite/', $path)) {
            $path .= '; SameSite=None ; secure';
			//$path .= '; SameSite=None ';
            $this->setOption('session.cookie_path', $path);
        }
		*/
		//PHP 7.3+ compatible 
		$options = session_get_cookie_params();  
		$options['samesite'] = 'None';
		$options['secure'] = true;
		unset($options['lifetime']); 
		$cookies = $_COOKIE;  	
		foreach ($cookies as $key => $value)
		{
			if (!preg_match('/cart/', $key))
				setcookie($key, $value, $options);
		}
		
        return $this;
    }
}

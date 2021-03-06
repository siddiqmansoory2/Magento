<?php

namespace PayUIndia\Payu\Controller\Standard;

class Redirect extends \PayUIndia\Payu\Controller\PayuAbstract {

    public function execute() {
        if (!$this->getRequest()->isAjax()) {
            $this->_cancelPayment();
            $this->_checkoutSession->restoreQuote();
            $this->getResponse()->setRedirect(
                    $this->getCheckoutHelper()->getUrl('checkout')
            );
        }
		/*
        $quote = $this->getQuote();
        $email = $this->getRequest()->getParam('email');
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->getCheckoutSession()->loadCustomerQuote();
            $quote->updateCustomerData($this->getQuote()->getCustomer());
        } else {
            $quote->setCustomerEmail($email);
        }

        if ($this->getCustomerSession()->isLoggedIn()) {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
        } else {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        }*/
		//$quote->setCanSendNewEmailFlag(false);
		//$quote->setEmailSent(false);
		//$quote->setSendEmail(false);
		//$quote->setPayment($this->getPaymentMethod()->getMethodInstance());
        //$quote->setCustomerEmail($email);		
        //$quote->save();      
		

        $params = [];
        $params["fields"] = $this->getPaymentMethod()->buildCheckoutRequest();
        $params["url"] = $this->getPaymentMethod()->getCgiUrl();

        return $this->resultJsonFactory->create()->setData($params);
    }

}

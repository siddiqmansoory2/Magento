<?php
/**
 * Copyright © PapaReferFriendBlock All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\PapaReferFriendBlock\Block;

class Homepage extends \Magento\Framework\View\Element\Template
{
    
    protected $_customerSession;    // don't name this `$_session` since it is already used in \Magento\Customer\Model\Session and your override would cause problems

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_customerSession = $session;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function isCustomerLoggedIn()
    {
        //Your block code
        return __('Hello Developer! This how to get the storename: %1 and this is the way to build a url: %2', $this->_customerSession->isLoggedIn(), $this->getUrl('contacts'));
    }
}


<?php

namespace Dolphin\Walletrewardpoints\Block\Customer;

use Dolphin\Walletrewardpoints\Helper\Data;
use Dolphin\Walletrewardpoints\Model\InviteFriendFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class InviteFriendHistory extends \Magento\Framework\View\Element\Template
{
    protected $inviteFriendCollection;

    public function __construct(
        Context $context,
        Session $customerSession,
        InviteFriendFactory $inviteFriendFactory,
        Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->inviteFriendFactory = $inviteFriendFactory;
        $this->helper = $helper;
        parent::__construct(
            $context
        );
    }

    public function getInviteFriendHistory()
    {
        $customer_id = $this->helper->getCustomerIdFromSession();
        if (!$customer_id) {
            return false;
        }
        if (!$this->inviteFriendCollection) {
            $this->inviteFriendCollection = $this->inviteFriendFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customer_id)
                ->setOrder(
                    'invite_date',
                    'desc'
                );
        }
        return $this->inviteFriendCollection;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getInviteFriendHistory()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'invite.friend.history.pager'
            )->setCollection(
                $this->getInviteFriendHistory()
            );
            $this->setChild('pager', $pager);
            $this->getInviteFriendHistory()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}

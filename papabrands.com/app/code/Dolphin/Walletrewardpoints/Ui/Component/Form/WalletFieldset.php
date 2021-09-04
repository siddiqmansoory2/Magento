<?php

namespace Dolphin\Walletrewardpoints\Ui\Component\Form;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;

class WalletFieldset extends Fieldset implements ComponentVisibilityInterface
{
    public function __construct(
        ContextInterface $context,
        Http $request,
        array $components = [],
        array $data = []
    ) {
        $this->context = $context;
        $this->request = $request;
        parent::__construct($context, $components, $data);
    }

    public function isComponentVisible(): bool
    {
        $visible = false;
        $customer_id = $this->request->getParam('id');
        if ($customer_id) {
            $visible = true;
        }
        return (bool) $visible;
    }
}

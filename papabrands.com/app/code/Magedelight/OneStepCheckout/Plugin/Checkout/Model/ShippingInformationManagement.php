<?php
namespace Magedelight\OneStepCheckout\Plugin\Checkout\Model;

use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magedelight\OneStepCheckout\Helper\Data as OscHelper;
use Magento\Framework\Encryption\EncryptorInterface;

class ShippingInformationManagement
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var OscHelper
     */
    protected $helper;

    /**
     * @var EncryptorInterface
     */
    protected $encrypt;

    /**
     * ShippingInformationManagement constructor.
     *
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        OscHelper $helper,
        EncryptorInterface $encrypt
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->encrypt = $encrypt;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        $extAttributes = $addressInformation->getExtensionAttributes();
        if ($extAttributes->getMdoscExtraFeeChecked()) {
            $quote->setData('mdosc_extra_fee_checked', $extAttributes->getMdoscExtraFeeChecked());
        }
    }
}

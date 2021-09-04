<?php

namespace Dolphin\Walletrewardpoints\Controller\Customer;

use Dolphin\Walletrewardpoints\Helper\Data;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Io\File as IoFiles;
use Magento\Framework\View\Asset\Repository as AssetRepo;

class Purchasecredit extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context,
        CartModel $cart,
        DirectoryList $directoryList,
        IoFiles $files,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        ProductModel $product,
        CustomerModel $customerModel,
        AssetRepo $assetRepo,
        JsonFactory $resultJsonFactory,
        Data $helper,
        CatalogSession $catalogSession
    ) {
        $this->cart = $cart;
        $this->directoryList = $directoryList;
        $this->file = $files;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->product = $product;
        $this->customerModel = $customerModel;
        $this->assetRepo = $assetRepo;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->catalogSession = $catalogSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = (array) $this->getRequest()->getPost();
        $data['result'] = "fail";
        $qty = $data["credit"];
        $cid = $this->customerSession->getId();
        $customernew = $this->customerModel->load($cid);
        $walletCredit = $customernew->getWalletCredit();
        $customerMaxCredit = $this->helper->getCustomerMaxCredit();
        if ($customerMaxCredit && ($walletCredit + $qty) > $customerMaxCredit) {
            $this->messageManager->addNotice(
                __(
                    'Your maximum credit limit is %1.',
                    $customerMaxCredit
                )
            );
            return $resultJson->setData($data);
        }
        $product_sku = 'rewardpoints';
        if (!$this->product->getIdBySku($product_sku)) {
            try {
                $imagePath = $this->assetRepo->getUrl('Dolphin_Walletrewardpoints::images/coin.png');
                $tmpDir = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . 'tmp';
                $this->file->checkAndCreateFolder($tmpDir);
                $imgFileInfo = $this->file->getPathInfo($imagePath);
                $imgBasename = $imgFileInfo['basename'];
                $newFileName = $tmpDir . $imgBasename;
                $this->file->read($imagePath, $newFileName);
                $this->product
                    ->setWebsiteIds([1])
                    ->setAttributeSetId(4)
                    ->setTypeId('virtual')
                    ->setCreatedAt(strtotime('now'))
                    ->setSku('rewardpoints')
                    ->setName('Credit')
                    ->setStatus(1)
                    ->setTaxClassId(0)
                    ->setPrice(1)
                    ->setMetaTitle('Credit')
                    ->setMetaKeyword('Credit')
                    ->setMetaDescription('Credit')
                    ->setDescription('Credit')
                    ->setShortDescription('Credit')
                    ->setMediaGallery(['images' => [], 'values' => []])
                    ->addImageToMediaGallery(
                        $newFileName,
                        ['image', 'thumbnail', 'small_image'],
                        false,
                        false
                    )
                    ->setStockData([
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 0,
                        'min_sale_qty' => 1,
                        'is_in_stock' => 1,
                        'qty' => 9999,
                    ]);
                $this->product->save();
                $this->file->rm($newFileName);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        $id = $this->product->getIdBySku($product_sku);
        if ($qty > 0) {
            try {
                $product = $this->product->load($id);
                $this->cart->addProduct($product, ['qty' => $qty]);
                $this->cart->save();
                $this->checkoutSession->setCartWasUpdated(true);
                $this->messageManager->addSuccess(
                    __(
                        '%1 credit has been added into your cart.',
                        $qty
                    )
                );
                $applyCredit = $this->catalogSession->getApplyCredit();
                if ($applyCredit) {
                    $this->messageManager->addError(
                        __('Credit is not applied on "' . $product->getName() . '" product.')
                    );
                    $this->catalogSession->setApplyCredit(0);
                }
                $data['result'] = "success";
                return $resultJson->setData($data);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultJson->setData($data);
    }
}

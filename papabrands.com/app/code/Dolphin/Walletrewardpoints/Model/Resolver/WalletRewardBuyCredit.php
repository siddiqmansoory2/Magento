<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Dolphin\Walletrewardpoints\Helper\Data;
use Dolphin\Walletrewardpoints\Model\Sales\Total\CreditDiscount;
use Dolphin\Walletrewardpoints\Model\Withdraw;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as IoFiles;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\Asset\Repository as AssetRepo;

class WalletRewardBuyCredit implements ResolverInterface
{
    protected $withdraw;
    protected $creditDiscountModel;

    public function __construct(
        CatalogSession $catalogSession,
        CheckoutSession $checkoutSession,
        CartModel $cart,
        IoFiles $files,
        DirectoryList $directoryList,
        AssetRepo $assetRepo,
        ProductModel $product,
        Data $helper,
        CustomerModel $customerModel,
        CreditDiscount $creditDiscountModel,
        Withdraw $withdraw
    ) {
        $this->catalogSession = $catalogSession;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->file = $files;
        $this->directoryList = $directoryList;
        $this->assetRepo = $assetRepo;
        $this->product = $product;
        $this->helper = $helper;
        $this->customerModel = $customerModel;
        $this->creditDiscountModel = $creditDiscountModel;
        $this->withdraw = $withdraw;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (!is_int($args['credit']) || $args['credit'] <= 0) {
            throw new GraphQlInputException(__('Specify the credit Int value greater then zero.'));
        }
        $qty = $args["credit"];
        $cid = (int) $context->getUserId();
        $customernew = $this->customerModel->load($cid);
        $walletCredit = $customernew->getWalletCredit();
        $customerMaxCredit = $this->helper->getCustomerMaxCredit();
        if ($customerMaxCredit && ($walletCredit + $qty) > $customerMaxCredit) {
            $showMsg = (__('Your maximum credit limit is %1.', $customerMaxCredit));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
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
                $showMsg = (__($e->getMessage()));
                return [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
            }
        }
        $id = $this->product->getIdBySku($product_sku);
        if ($qty > 0) {
            try {
                $product = $this->product->load($id);
                $this->cart->addProduct($product, ['qty' => $qty]);
                $this->cart->save();
                $this->checkoutSession->setCartWasUpdated(true);
                $showMsg = (__('%1 credit has been added into your cart.', $qty));
                $applyCredit = $this->catalogSession->getApplyCredit();
                if ($applyCredit) {
                    $showMsg = (__('Credit is not applied on "' . $product->getName() . '" product.'));
                    $this->catalogSession->setApplyCredit(0);

                }
                return [
                    'status' => "SUCCESS",
                    'message' => $showMsg,
                ];
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $showMsg = (__('Something went wrong while saving your subscription.'));
                return [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
            }
        } else {
            $showMsg = (__('Please add product quantity.'));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        }
    }
}

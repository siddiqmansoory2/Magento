<?php
namespace MageArray\CheckDelivery\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package MageArray\CheckDelivery\Helper
 */
class Data extends AbstractHelper
{
    
    const CONFIG_ENABLE = 'checkDelivery/setting/enable';
   
    const CONFIG_POSTCODES = 'checkDelivery/setting/postcodes';
    
    const CONFIG_SUCCESS_MESSAGE = 'checkDelivery/setting/success_message';
    
    const CONFIG_ERROR_MESSAGE = 'checkDelivery/setting/error_message';

    /**
     * @var ScopeConfig
     */
    protected $_scopeConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
    }

    /**
     * @param $storePath
     * @return mixed
     */
    public function getStoreConfig($storePath)
    {
        return $this->_scopeConfig->getValue(
            $storePath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPostcodes()
    {
        return trim(self::getStoreConfig(self::CONFIG_POSTCODES));
    }

    /**
     * @return mixed
     */
    public function getSuccessMessage()
    {
        return self::getStoreConfig(self::CONFIG_SUCCESS_MESSAGE);
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return self::getStoreConfig(self::CONFIG_ERROR_MESSAGE);
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return self::getStoreConfig(self::CONFIG_ENABLE);
    }
}

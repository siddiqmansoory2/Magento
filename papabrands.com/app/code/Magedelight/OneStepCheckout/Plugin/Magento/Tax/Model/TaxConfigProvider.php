<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\OneStepCheckout\Plugin\Magento\Tax\Model;

use Magedelight\OneStepCheckout\Model\CountryByWebsite;

class TaxConfigProvider
{
    protected $countrycode;
    /**
     * @param \Magedelight\OneStepCheckout\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\OneStepCheckout\Helper\Data $helper,
        CountryByWebsite $countrycode
    ) {
        $this->helper = $helper;
        $this->countrycode = $countrycode;
    }

    /**
     * @param $subject
     * @param $result
     * @return array
     */
    public function afterGetConfig($subject, $result)
    {
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }

        if ($this->helper->getShippingAddressFieldConfig()) {
            $config = $this->helper->getShippingAddressFieldConfig();
            if (isset($config['rows']['country_id']['default'])) {
                if ($config['rows']['country_id']['default']) {
                    if ($config['rows']['country_id']['default'] == 'selected="selected"') {
                        $coreValue = $this->countrycode->getCountryByWebsite();
                        $result['defaultCountryId'] = $coreValue;
                    } else {
                        $result['defaultCountryId'] = $config['rows']['country_id']['default'];
                    }
                    
                }
            }

            if (isset($config['rows']['region_id']['default'])) {
                if ($config['rows']['region_id']['default']) {
                    $result['defaultRegionId'] = $config['rows']['region_id']['default'];
                }
            }

            if (isset($config['rows']['postcode']['default'])) {
                if ($config['rows']['postcode']['default']) {
                    $result['defaultPostcode'] = $config['rows']['postcode']['default'];
                }
            }
        }
        return $result;
    }
}

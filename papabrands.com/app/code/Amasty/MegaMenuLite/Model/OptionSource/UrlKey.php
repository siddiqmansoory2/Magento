<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class UrlKey implements OptionSourceInterface
{
    const NO = 0;

    const LINK = 1;

    const EXTERNAL_URL = 4;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NO, 'label' => __('Choose an option')],
            ['value' => self::LINK, 'label' => __('Internal URL')],
            ['value' => self::EXTERNAL_URL, 'label' => __('External URL')]
        ];
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getLabelByValue($value)
    {
        foreach ($this->toOptionArray() as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }

        return '';
    }

    public function getValues(): array
    {
        return array_column($this->toOptionArray(), 'value');
    }
}

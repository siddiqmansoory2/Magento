<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types = 1);

namespace Amasty\MegaMenuLite\Model\Provider;

class FieldsByStore
{
    /**
     * @var array
     */
    private $fieldsByStoreCustom;

    /**
     * @var array
     */
    private $fieldsByStoreCategory;

    public function __construct(
        array $fieldsByStoreCustom = [],
        array $fieldsByStoreCategory = []
    ) {
        $this->fieldsByStoreCustom = $fieldsByStoreCustom;
        $this->fieldsByStoreCategory = $fieldsByStoreCategory;
    }

    public function getCustomFields(): array
    {
        return $this->fieldsByStoreCustom;
    }

    public function getCategoryFields(): array
    {
        return $this->fieldsByStoreCategory;
    }
}

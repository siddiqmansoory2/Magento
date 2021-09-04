<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\Backend\DataProvider;

interface DataCollectorInterface
{
    public function execute(array $data, int $storeId, int $entityId): array;
}

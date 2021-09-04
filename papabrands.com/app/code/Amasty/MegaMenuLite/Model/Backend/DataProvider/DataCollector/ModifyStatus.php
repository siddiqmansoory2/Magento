<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\Backend\DataProvider\DataCollector;

use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Backend\DataProvider\DataCollectorInterface;
use Amasty\MegaMenuLite\Model\OptionSource\Status;
use Amasty\MegaMenuLite\Model\OptionSource\UrlKey;

class ModifyStatus implements DataCollectorInterface
{
    /**
     * @var UrlKey
     */
    private $urlKey;

    public function __construct(UrlKey $urlKey)
    {
        $this->urlKey = $urlKey;
    }

    public function execute(array $data, int $storeId, int $entityId): array
    {
        if (!in_array($data[LinkInterface::TYPE], $this->urlKey->getValues())) {
            $data[ItemInterface::STATUS] = Status::DISABLED;
        }

        return $data;
    }
}

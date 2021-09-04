<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types = 1);

namespace Amasty\MegaMenu\Observer;

use Amasty\MegaMenu\Model\Menu\ContainerData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateNodeData implements ObserverInterface
{
    /**
     * @var ContainerData
     */
    private $containerData;

    public function __construct(ContainerData $containerData)
    {
        $this->containerData = $containerData;
    }

    public function execute(Observer $observer): void
    {
        $transportObject = $observer->getData('transport_object');
        $node = $observer->getData('node');
        
        $this->containerData->setNodeDataToObject($node, $transportObject);
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Controller\Adminhtml\Link;

use Magento\Backend\App\Action;

class NewAction extends Edit
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_MegaMenu::menu_links';
}

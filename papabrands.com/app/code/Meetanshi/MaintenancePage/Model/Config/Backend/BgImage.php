<?php

namespace Meetanshi\MaintenancePage\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Image;
use Meetanshi\MaintenancePage\Helper\Data;

class BgImage extends Image
{
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(Data::BGIMAGES_MEDIA_DIR));
    }

    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }
}

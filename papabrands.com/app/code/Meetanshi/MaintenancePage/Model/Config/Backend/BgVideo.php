<?php

namespace Meetanshi\MaintenancePage\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Meetanshi\MaintenancePage\Helper\Data;

class BgVideo extends File
{
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(Data::BGVIDEOS_MEDIA_DIR));
    }

    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    protected function _getAllowedExtensions()
    {
        return ['mp4'];
    }
}

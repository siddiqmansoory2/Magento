<?php
/**
 * @copyright: Copyright © 2015 Firebear Studio. All rights reserved.
 * @author: Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Source\Type;

use Firebear\ImportExport\Model\Filesystem\Io\Sftp as IoSftp;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Sftp
 *
 * @package Firebear\ImportExport\Model\Source\Type
 */
class Sftp extends AbstractType
{
    const SFTP_SOURCE = IoSftp::class;

    /**
     * @var string
     */
    protected $code = 'sftp';

    /**
     * @return string
     */
    public function getTempFilePath()
    {
        $fileName = basename($this->getData($this->code . '_file_path'));
        return $this->directory->getAbsolutePath($this->getImportPath() . '/' . $fileName);
    }

    /**
     * Remove uploaded temporary file
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function resetSource()
    {
        return $this->directory->delete(
            $this->getTempFilePath()
        );
    }

    /**
     * Download remote source file to temporary directory
     *
     * @return string
     * @throws LocalizedException
     */
    public function uploadSource()
    {
        if ($client = $this->_getSourceClient()) {
            $filePath = $this->getTempFilePath();
            if (!$this->directory->isExist($filePath)) {
                $remoteFilePath = $this->getData($this->code . '_file_path');
                $this->directory->create(dirname($filePath));
                $result = $client->read($remoteFilePath, $filePath);
                if (!$result) {
                    throw new LocalizedException(__("File not found"));
                }
            }
            return $filePath;
        }
        throw new  LocalizedException(__("Can't initialize %s client", $this->code));
    }

    /**
     * Prepare and return SFTP client
     *
     * @return \Firebear\ImportExport\Model\Filesystem\Io\Ftp
     * @throws LocalizedException
     */
    protected function _getSourceClient()
    {
        if (!$this->getClient()) {
            if ($this->getData('host')
                && $this->getData('port')
                && $this->getData('username')
                && $this->getData('password')) {
                $settings = $this->getData();
            } else {
                $settings = $this->scopeConfig->getValue(
                    'firebear_importexport/sftp',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }
            $settings['passive'] = true;
            try {
                $connection = $this->factory->create(self::SFTP_SOURCE);
                $connection->open(
                    $settings
                );
                $this->client = $connection;
            } catch (\Exception $e) {
                throw new  LocalizedException(__($e->getMessage()));
            }
        }

        return $this->getClient();
    }

    /**
     * Download remote images to temporary media directory
     *
     * @param $importImage
     * @param $imageSting
     *
     * @throws LocalizedException
     */
    public function importImage($importImage, $imageSting)
    {
        if ($client = $this->_getSourceClient()) {
            $sourceFilePath = $this->getData($this->code . '_file_path');
            $sourceDirName = dirname($sourceFilePath);
            $filePath = $this->directory->getAbsolutePath($this->getMediaImportPath() . $imageSting);
            $dirname = dirname($filePath);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0775, true);
            }
            if ($filePath) {
                $result = $client->read($sourceDirName . '/' . $importImage, $filePath);
            }
        }
    }

    /**
     * Check if remote file was modified since the last import
     *
     * @param int $timestamp
     * @return bool|int
     */
    public function checkModified($timestamp)
    {
        if ($client = $this->_getSourceClient()) {
            $sourceFilePath = $this->getData($this->code . '_file_path');

            if (!$this->metadata) {
                $this->metadata['modified'] = $client->mdtm($sourceFilePath);
            }

            $modified = $this->metadata['modified'];

            return ($timestamp != $this->metadata['modified']) ? $modified : false;
        }

        return false;
    }

    /**
     * @param $model
     * @return string
     * @throws LocalizedException
     */
    public function run($model)
    {
        $result = true;
        $errors = [];
        $path = '';
        try {
            $this->setExportModel($model);
            $name = 'export_' . $this->timezone->date()->format('Y_m_d_H_i_s');
            $path = AbstractType::EXPORT_DIR . "/" . $name;
            if ($this->writeFile($path)) {
                if ($client = $this->_getSourceClient()) {
                    $fileFormat = $model->getFileFormat();
                    $currentDate = "";
                    if ($this->getData('date_format')) {
                        $format = $this->getData('date_format') ?? 'Y-m-d-hi';
                        $currentDate = "-" . $this->timezone->date()->format($format);
                    }
                    $info = pathinfo($this->getData('file_path'));
                    $sourceFilePath =  $info['dirname'] . '/' . $info['filename'] .
                        $currentDate . '.' . $info['extension'];

                    $filePath = $this->directory->getAbsolutePath($path);
                    $client->mkdir($info['dirname']);
                    $result = $client->write($sourceFilePath, $filePath);
                    if (!$result) {
                        $result = false;
                        $errors[] = __('File not found');
                    }
                    $client->close();
                } else {
                    $result = false;
                    $errors[] = __("Can't initialize %s client", $this->code);
                }
            }
        } catch (\Exception $e) {
            $result = false;
            $errors[] = __('Folder for import / export don\'t have enough permissions! Please set 775');
        }

        return [$result, $path, $errors];
    }
}

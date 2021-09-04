<?php

namespace Meetanshi\MaintenancePage\Cron;

use Meetanshi\MaintenancePage\Helper\Data;
use Meetanshi\MaintenancePage\Helper\SendEmail;

class Daily
{
    private $helper;

    private $emailHelper;

    public function __construct(
        Data $helperData,
        SendEmail $sendEmailHelper
    ) {
        $this->helper = $helperData;
        $this->emailHelper = $sendEmailHelper;
    }

    public function execute()
    {
        $isEnabled = $this->helper->isModuleEnabled();
        $canAlertAdmin = $this->helper->canAlertToAdmin();
        if ($isEnabled && $canAlertAdmin) {
            $this->emailHelper->sendAlertEmailToAdmin();
        }

        return $this;
    }
}
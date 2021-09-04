<?php

namespace Meetanshi\MaintenancePage\Helper;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class SendEmail extends AbstractHelper
{
    private $messageManager;

    private $helper;

    private $storeManager;

    private $inlineTranslation;

    private $transportBuilder;

    public function __construct(
        ManagerInterface $messageManager,
        Data $helperData,
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->helper = $helperData;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
    }

    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    public function generateTemplate($templateVars, $receiverInfo, $templateId, $emailSender)
    {
        $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFrom($emailSender)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }


    public function notify($name, $email, $templateId, $templateVars, $emailSender)
    {
        $receiverInfo = [
            'name' => $name,
            'email' => $email
        ];

        try {
            $this->inlineTranslation->suspend();
            $this->generateTemplate($templateVars, $receiverInfo, $templateId, $emailSender);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $this;
    }

    public function sendAlertEmailToAdmin($toName = 'Admin', $toEmail = '', $endTime = '')
    {
        $endTime = ($endTime) ? $endTime : $this->helper->getTimerEndTime();
        $toName = ($toName) ? $toName : 'Admin';
        $toEmail = ($toEmail) ? $toEmail : $this->helper->getAlertEmailReceiver();
        $toEmail = ($toEmail) ? explode(",", $toEmail) : $toEmail;
        $templateId = $this->helper->getAlertEmailTemplate();
        $templateVars['dateTime'] = $endTime;
        $emailSender = $this->helper->getAlertEmailSender();

        if ($toName && $toEmail && $templateId && $templateVars && $emailSender) {
            try {
                $this->notify($toName, $toEmail, $templateId, $templateVars, $emailSender);
                return true;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                return false;
            }
        }
    }
}

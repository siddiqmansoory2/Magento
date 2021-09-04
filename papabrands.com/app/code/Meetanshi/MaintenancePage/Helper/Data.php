<?php

namespace Meetanshi\MaintenancePage\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const MODULE_CODE = 'maintenancepage';
    const ICONS_MEDIA_DIR = 'maintenancepage/icons/';
    const BGIMAGES_MEDIA_DIR = 'maintenancepage/bgimages/';
    const BGVIDEOS_MEDIA_DIR = 'maintenancepage/bgvideos/';

    const ENABLE_MODULE = 'maintenancepage/general_configuration/enable_disable';
    const WHITELIST_IPS = 'maintenancepage/general_configuration/whitelist_ips';
    const ALLOWED_URLS = 'maintenancepage/general_configuration/allowed_urls';
    const REDIRECT_TO_PAGE = 'maintenancepage/general_configuration/redirect_to_page';
    const REDIRECT_TO_PAGE_DEFAULT = 'maintenancepage';
    const PAGE_LAYOUT = 'maintenancepage/layout_configuration/layout';

    const BACKGROUND_TYPE = 'maintenancepage/background_configuration/background_type';
    const BACKGROUND_IMAGE = 'maintenancepage/background_configuration/bgimage';
    const BACKGROUND_VIDEO = 'maintenancepage/background_configuration/bgvideo';
    const BACKGROUND_OVERLAYER = 'maintenancepage/background_configuration/background_overlayer';

    const PAGE_TITLE = 'maintenancepage/text_content_configuration/page_title';
    const PAGE_DESC = 'maintenancepage/text_content_configuration/page_desc';

    const ENABLE_TIMER = 'maintenancepage/timer_configuration/enable_disable';
    const TIMER_TITLE = 'maintenancepage/timer_configuration/title';
    const TIMER_TITLE_COLOR = 'maintenancepage/timer_configuration/title_color';
    const TIMER_END_TIME = 'maintenancepage/timer_configuration/end_time';
    const DISABLE_MAINTENANCE_AFTER_END_TIME = 'maintenancepage/timer_configuration/disable_maintenance';

    const ENABLE_NEWSLETTER = 'maintenancepage/newsletter_configuration/enable_disable';
    const NEWSLETTER_TITLE = 'maintenancepage/newsletter_configuration/title';
    const NEWSLETTER_TITLE_COLOR = 'maintenancepage/newsletter_configuration/title_color';

    const ENABLE_SOCIAL = 'maintenancepage/social_configuration/enable_disable';
    const SOCIAL_TITLE = 'maintenancepage/social_configuration/title';
    const SOCIAL_TITLE_COLOR = 'maintenancepage/social_configuration/title_color';
    const FACEBOOK_LINK = 'maintenancepage/social_configuration/facebook_link';
    const GOOGLE_LINK = 'maintenancepage/social_configuration/google_link';
    const LINKEDIN_LINK = 'maintenancepage/social_configuration/linkedin_link';
    const TWITTER_LINK = 'maintenancepage/social_configuration/twitter_link';
    const YOUTUBE_LINK = 'maintenancepage/social_configuration/youtube_link';
    const INSTAGRAM_LINK = 'maintenancepage/social_configuration/instagram_link';
    const PINTEREST_LINK = 'maintenancepage/social_configuration/pinterest_link';

    const ICON = 'maintenancepage/logo_configuration/icon';
    const ICON_WIDTH = 'maintenancepage/logo_configuration/icon_width';
    const ICON_WIDTH_DEFAULT = 150;
    const ICON_HEIGHT = 'maintenancepage/logo_configuration/icon_height';
    const ICON_HEIGHT_DEFAULT = 150;

    const FOOTER_TEXT = 'maintenancepage/footer_configuration/footer_text';

    const ENABLE_ALERT = 'maintenancepage/alert_for_admin/enable_disable';
    const ALERT_EMAIL_SENDER = 'maintenancepage/alert_for_admin/email_sender';
    const ALERT_EMAIL_RECEIVER = 'maintenancepage/alert_for_admin/email_receiver';
    const ALERT_EMAIL_TEMPLATE = 'maintenancepage/alert_for_admin/email_template';
    const ALERT_NO_OF_DAYS_TO_NOTIFY = 'maintenancepage/alert_for_admin/no_of_days_to_notify';

    const ENABLE_GOOGLE_ANALYTICS = 'maintenancepage/google_analytics/enable_disable';
    const GOOGLE_ANALYTICS_TRACKING_ID = 'maintenancepage/google_analytics/tracking_id';

    const PAGE_DESIGN = 'maintenancepage/general_configuration/page_design';

    protected $scopeConfig;

    private $urlInterface;

    private $dateTime;

    private $storeManager;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlInterface,
        DateTime $dateTime,
        StoreManagerInterface $storeManagerInterface
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->urlInterface = $urlInterface;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManagerInterface;
    }

    public function getUrlInterface()
    {
        return $this->urlInterface;
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLE_MODULE, ScopeInterface::SCOPE_STORE);
    }

    public function getPageLayout()
    {
        return $this->scopeConfig->getValue(self::PAGE_LAYOUT, ScopeInterface::SCOPE_STORE);
    }

    public function getWhitelistIps()
    {
        return explode(
            ",",
            $this->scopeConfig->getValue(self::WHITELIST_IPS, ScopeInterface::SCOPE_STORE)
        );
    }

    public function getAllowedUrls()
    {
        $ret = [];
        $allowedUrls = $this->scopeConfig->getValue(self::ALLOWED_URLS, ScopeInterface::SCOPE_STORE);
        $allowedUrlsArray = ($allowedUrls) ? explode(",", $allowedUrls) : [];
        if (!empty($allowedUrlsArray)) {
            foreach ($allowedUrlsArray as $allowedUrl) {
                $ret[] = $this->urlInterface->getUrl($allowedUrl);
            }
        }
        $ret[] = $this->urlInterface->getUrl($this->getRedirectToPage());

        return $ret;
    }

    public function getRedirectToPage($toUrl = false)
    {
        $ret = $this->scopeConfig->getValue(self::REDIRECT_TO_PAGE, ScopeInterface::SCOPE_STORE);
        $ret = ($ret) ? $ret : self::REDIRECT_TO_PAGE_DEFAULT;
        return ($toUrl) ? $this->urlInterface->getUrl($ret) : $ret;
    }

    public function getBackgroundType()
    {
        return $this->scopeConfig->getValue(self::BACKGROUND_TYPE, ScopeInterface::SCOPE_STORE);
    }

    public function getBackgroundImage()
    {
        return $this->scopeConfig->getValue(self::BACKGROUND_IMAGE, ScopeInterface::SCOPE_STORE);
    }

    public function getBackgroundImageUrl()
    {
        return $this->getMediaUrl() . self::BGIMAGES_MEDIA_DIR . $this->getBackgroundImage();
    }

    public function getBackgroundVideo()
    {
        return $this->scopeConfig->getValue(self::BACKGROUND_VIDEO, ScopeInterface::SCOPE_STORE);
    }

    public function getBackgroundVideoUrl()
    {
        return $this->getMediaUrl() . self::BGVIDEOS_MEDIA_DIR . $this->getBackgroundVideo();
    }

    public function getBackgroundOverLayer()
    {
        return $this->scopeConfig->getValue(self::BACKGROUND_OVERLAYER, ScopeInterface::SCOPE_STORE);
    }

    public function getPageTitle()
    {
        return $this->scopeConfig->getValue(self::PAGE_TITLE, ScopeInterface::SCOPE_STORE);
    }

    public function getPageDesc()
    {
        return $this->scopeConfig->getValue(self::PAGE_DESC, ScopeInterface::SCOPE_STORE);
    }

    public function isTimerEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLE_TIMER, ScopeInterface::SCOPE_STORE);
    }

    public function getTimerTitle()
    {
        return $this->scopeConfig->getValue(self::TIMER_TITLE, ScopeInterface::SCOPE_STORE);
    }

    public function getTimerTitleColor()
    {
        $ret = $this->scopeConfig->getValue(self::TIMER_TITLE_COLOR, ScopeInterface::SCOPE_STORE);
        return ($ret) ? $ret : '#000';
    }

    public function getTimerEndTime($strToTime = false)
    {
        $ret = $this->scopeConfig->getValue(self::TIMER_END_TIME, ScopeInterface::SCOPE_STORE);
        return ($ret && $strToTime) ? strtotime($ret) : $ret;
    }

    public function disableMaintenanceAfterEndTime()
    {
        return $this->scopeConfig->getValue(self::DISABLE_MAINTENANCE_AFTER_END_TIME, ScopeInterface::SCOPE_STORE);
    }

    public function isNewsletterEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLE_NEWSLETTER, ScopeInterface::SCOPE_STORE);
    }

    public function getNewsletterTitle()
    {
        return $this->scopeConfig->getValue(self::NEWSLETTER_TITLE, ScopeInterface::SCOPE_STORE);
    }

    public function getNewsletterTitleColor()
    {
        return $this->scopeConfig->getValue(self::NEWSLETTER_TITLE_COLOR, ScopeInterface::SCOPE_STORE);
    }

    public function isSocialEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLE_SOCIAL, ScopeInterface::SCOPE_STORE);
    }

    public function getSocialTitle()
    {
        return $this->scopeConfig->getValue(self::SOCIAL_TITLE, ScopeInterface::SCOPE_STORE);
    }

    public function getSocialTitleColor()
    {
        return $this->scopeConfig->getValue(self::SOCIAL_TITLE_COLOR, ScopeInterface::SCOPE_STORE);
    }

    public function getFacebookLink()
    {
        return $this->scopeConfig->getValue(self::FACEBOOK_LINK, ScopeInterface::SCOPE_STORE);
    }

    public function canDisplayFacebookLink()
    {
        return $this->getFacebookLink();
    }

    public function getGoogleLink()
    {
        return $this->scopeConfig->getValue(self::GOOGLE_LINK, ScopeInterface::SCOPE_STORE);
    }

    public function canDisplayGoogleLink()
    {
        return $this->getGoogleLink();
    }

    public function getLinkedinLink()
    {
        return $this->scopeConfig->getValue(self::LINKEDIN_LINK, ScopeInterface::SCOPE_STORE);
    }

    public function canDisplayLinkedinLink()
    {
        return $this->getLinkedinLink();
    }

    public function getTwitterLink()
    {
        return $this->scopeConfig->getValue(self::TWITTER_LINK, ScopeInterface::SCOPE_STORE);
    }

    public function canDisplayTwitterLink()
    {
        return $this->getTwitterLink();
    }

    public function getYoutubeLink()
    {
        return $this->scopeConfig->getValue(self::YOUTUBE_LINK, ScopeInterface::SCOPE_STORE);
    }

    public function canDisplayYoutubeLink()
    {
        return $this->getYoutubeLink();
    }

    public function getInstagramLink()
    {
        return $this->scopeConfig->getValue(self::INSTAGRAM_LINK, ScopeInterface::SCOPE_STORE);
    }

    public function canDisplayInstagramLink()
    {
        return $this->getInstagramLink();
    }

    public function getPinterestLink()
    {
        return $this->scopeConfig->getValue(self::PINTEREST_LINK, ScopeInterface::SCOPE_STORE);
    }

    public function canDisplayPinterestLink()
    {
        return $this->getPinterestLink();
    }

    public function getIcon()
    {
        return $this->scopeConfig->getValue(self::ICON, ScopeInterface::SCOPE_STORE);
    }

    public function getIconUrl()
    {
        return $this->getMediaUrl() . self::ICONS_MEDIA_DIR . $this->getIcon();
    }

    public function getIconWidth()
    {
        $ret = $this->scopeConfig->getValue(self::ICON_WIDTH, ScopeInterface::SCOPE_STORE);
        return ($ret) ? $ret : self::ICON_WIDTH_DEFAULT;
    }

    public function getIconHeight()
    {
        $ret = $this->scopeConfig->getValue(self::ICON_HEIGHT, ScopeInterface::SCOPE_STORE);
        return ($ret) ? $ret : self::ICON_HEIGHT_DEFAULT;
    }

    public function getFooterText()
    {
        return $this->scopeConfig->getValue(self::FOOTER_TEXT, ScopeInterface::SCOPE_STORE);
    }

    public function getCurrentDateTime($strToTime = false)
    {
         $date = $this->dateTime->gmtDate();
        return ($strToTime) ? strtotime($date) : $date;
    }

    public function isAlertEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLE_ALERT, ScopeInterface::SCOPE_STORE);
    }

    public function getAlertEmailSender()
    {
        return $this->scopeConfig->getValue(self::ALERT_EMAIL_SENDER, ScopeInterface::SCOPE_STORE);
    }

    public function getAlertEmailReceiver()
    {
        return $this->scopeConfig->getValue(self::ALERT_EMAIL_RECEIVER, ScopeInterface::SCOPE_STORE);
    }

    public function getAlertEmailTemplate()
    {
        return $this->scopeConfig->getValue(self::ALERT_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
    }

    public function getAlertNoOfDaysToNotify()
    {
        return $this->scopeConfig->getValue(self::ALERT_NO_OF_DAYS_TO_NOTIFY, ScopeInterface::SCOPE_STORE);
    }

    public function isGoogleAnalyticsEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLE_GOOGLE_ANALYTICS, ScopeInterface::SCOPE_STORE);
    }

    public function getGoogleAnalyticsTrackingId()
    {
        return $this->scopeConfig->getValue(self::GOOGLE_ANALYTICS_TRACKING_ID, ScopeInterface::SCOPE_STORE);
    }

    public function canDisableMaintenanceMode()
    {
        $endTime = $this->getTimerEndTime(true);
        $currentTime = $this->getCurrentDateTime(true);

        return ($this->isTimerEnabled() && $this->disableMaintenanceAfterEndTime() && $endTime <= $currentTime);
    }

    public function canAlertToAdmin()
    {
        $currentDateTime = $this->getCurrentDateTime();
        $timerEndTime = $this->getTimerEndTime();
        $alertNoOfDays = $this->getAlertNoOfDaysToNotify();
        $daysInAdvance = '-' . $alertNoOfDays . ' day';
        $notifyDateTime = date('Y-m-d H:i:s', strtotime($daysInAdvance, strtotime($timerEndTime)));
        $currentDay = date("d", strtotime($currentDateTime));
        $notifyDay = date("d", strtotime($notifyDateTime));
        $isEnabled = $this->isAlertEnabled();

        return ($isEnabled && $alertNoOfDays && $timerEndTime && $currentDay == $notifyDay);
    }
    public function getPageDesign(){
        return $this->scopeConfig->getValue(self::PAGE_DESIGN, ScopeInterface::SCOPE_STORE);
    }
}
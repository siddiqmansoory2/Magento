<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OneStepCheckout\Model;

/**
 * Class DeliveryDate
 * @package Magedelight\OneStepCheckout\Model
 */
class DeliveryDate
{
    /**
     * @var \Magedelight\OneStepCheckout\Helper\Data
     */
    private $oscHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var Config\Source\TimeOptions
     */
    protected $timeConfig;

    /**
     * @var Config\Source\DayOptions
     */
    protected $dayConfig;

    /**
     * DeliveryDate constructor.
     * @param \Magedelight\OneStepCheckout\Helper\Data $oscHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Config\Source\TimeOptions $timeConfig
     * @param Config\Source\DayOptions $dayConfig
     */
    public function __construct(
        \Magedelight\OneStepCheckout\Helper\Data $oscHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\OneStepCheckout\Model\Config\Source\TimeOptions $timeConfig,
        \Magedelight\OneStepCheckout\Model\Config\Source\DayOptions $dayConfig
    ) {
        $this->oscHelper = $oscHelper;
        $this->date = $date;
        $this->timeConfig = $timeConfig;
        $this->dayConfig = $dayConfig;
    }

    /**
     * Get Delivery Time Slot
     *
     * @return array
     */
    public function getDeliveryTimeSlot()
    {
        $time = [];
        $timeSlot = $this->oscHelper->getDeliveryTimeSlot();
        $minInterval = (int) $this->oscHelper->getDeliveryMinInterval();
        $minDate = $this->date->gmtDate('d-m-Y', strtotime("+".$minInterval."days"));
        $maxInterval = (int) $this->oscHelper->getDeliveryMaxInterval();
        $countDates = 0;
        while ($countDates < $maxInterval) {
            $NewDate = $this->date->gmtDate('d-m-Y', strtotime($minDate."+".$countDates." days"));
            $dayofweek = date('w', strtotime($NewDate));
            if (in_array($dayofweek, array_column($timeSlot, 'day'))) {
                $day = $this->dayConfig->getDateLabelByValue($dayofweek);
                $time[strtotime($NewDate)]['day'] = $day;
                $time[strtotime($NewDate)]['date'] = $NewDate;
                $time[strtotime($NewDate)]['unix_date'] = strtotime($NewDate);
                $time[strtotime($NewDate)]['time'] = $this->getTimeSlotByDay($timeSlot, $dayofweek);
            }
            $countDates += 1;
        }
        return $time;
    }

    private function getTimeSlotByDay($timeSlot, $day)
    {
        $time = [];
        $filterBy = $day;
        $filterDays = array_filter($timeSlot, function ($var) use ($filterBy) {
            return ($var['day'] == $filterBy);
        });
        foreach ($filterDays as $filterDay) {
            $startTime = $this->timeConfig->getTimeLabelByValue($filterDay['start_time']);
            $endTime = $this->timeConfig->getTimeLabelByValue($filterDay['end_time']);
            $time[] = $startTime.' - '.$endTime;
        }
        return $time;
    }
}

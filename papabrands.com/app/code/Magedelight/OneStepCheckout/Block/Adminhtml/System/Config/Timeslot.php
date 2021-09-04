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
namespace Magedelight\OneStepCheckout\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class Timeslot
 * @package Magedelight\OneStepCheckout\Block\Adminhtml\System\Config
 */
class Timeslot extends AbstractFieldArray
{
    /**
     * @var null
     */
    protected $dayRenderer = null;

    /**
     * @var null
     */
    protected $startTimeRenderer = null;

    /**
     * @var null
     */
    protected $endTimeRenderer = null;

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'day',
            [
                'label' => __('Day'),
                'renderer' => $this->getDayRenderer(),
                'style' => 'width:266px'
            ]
        );

        $this->addColumn(
            'start_time',
            [
                'label' => __('Start Time'),
                'renderer' => $this->getStartTimeRenderer(),
                'style' => 'width:266px'
            ]
        );

        $this->addColumn(
            'end_time',
            [
                'label' => __('End Time'),
                'renderer' => $this->getEndTimeRenderer(),
                'style' => 'width:266px'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add TimeSlot');
    }

    /**
     * Day Renderer
     */
    protected function getDayRenderer()
    {
        if (!$this->dayRenderer) {
            $this->dayRenderer = $this->getLayout()->createBlock(
                Days::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->dayRenderer;
    }

    /**
     * Start Time Renderer
     */
    protected function getStartTimeRenderer()
    {
        if (!$this->startTimeRenderer) {
            $this->startTimeRenderer = $this->getLayout()->createBlock(
                Time::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->startTimeRenderer;
    }

    /**
     * End Time Renderer
     */
    protected function getEndTimeRenderer()
    {
        if (!$this->endTimeRenderer) {
            $this->endTimeRenderer = $this->getLayout()->createBlock(
                Time::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->endTimeRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $day = $row->getDay();
        $startTime = $row->getStartTime();
        $endTime = $row->getEndTime();
        $options = [];
        if ($day) {
            $options['option_' . $this->getDayRenderer()->calcOptionHash($day)]
                = 'selected="selected"';
        }
        if ($startTime) {
            $options['option_' . $this->getStartTimeRenderer()->calcOptionHash($startTime)]
                = 'selected="selected"';
        }
        if ($endTime) {
            $options['option_' . $this->getEndTimeRenderer()->calcOptionHash($endTime)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}

<?php


namespace Magedelight\OneStepCheckout\Model\Address\Form;

class DefaultWidth
{
    /**
     * @var $defaultWidth
     */
    protected $defaultWidth;

    /**
     * DefaultWidth constructor.
     * @param $defaultWidth
     */
    public function __construct(
        $defaultWidth
    ) {
        $this->defaultWidth = $defaultWidth;
    }

    /**
     * Get default field width
     *
     * @param string $rowId
     * @return int|null
     */
    public function getDefaultWidth($rowId)
    {
        return isset($this->defaultWidth[$rowId])
            ? $this->defaultWidth[$rowId]
            : 100;
    }
}

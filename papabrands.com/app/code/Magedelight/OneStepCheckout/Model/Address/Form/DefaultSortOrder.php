<?php


namespace Magedelight\OneStepCheckout\Model\Address\Form;

class DefaultSortOrder
{
    /**
     * @var $defaultSortOrder
     */
    protected $defaultSortOrder;

    /**
     * DefaultWidth constructor.
     * @param $defaultWidth
     */
    public function __construct(
        $defaultSortOrder
    ) {
        $this->defaultSortOrder = $defaultSortOrder;
    }

    /**
     * Get default row sort order
     *
     * @param string $rowId
     * @return int|null
     */
    public function getSortOrder($rowId)
    {
        return isset($this->defaultSortOrder[$rowId])
            ? $this->defaultSortOrder[$rowId]
            : null;
    }

    /**
     * Calculation of row sort order,
     * including non specified in $this->defaultFieldRowsSortOrder
     *
     * @param string $rowId
     * @param int|null $previous
     * @return int
     */
    public function calculateSortOrder($rowId, $previous = null)
    {
        $sortOrder = $this->getSortOrder($rowId);
        if (!$sortOrder) {
            return $previous !== null
                ? $previous + 1
                : max($this->defaultSortOrder) + 1;
        }
        return $sortOrder;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


// @codingStandardsIgnoreFile

declare(strict_types=1);

namespace Amasty\MegaMenu\Test\Unit\Model\Menu;

use Amasty\MegaMenu\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenu\Model\DataProvider\GetItemContentData;
use Amasty\MegaMenu\Model\Menu\Subcategory;
use Amasty\MegaMenu\Model\OptionSource\SubcategoriesPosition;
use Amasty\MegaMenu\Model\OptionSource\SubmenuType;
use Amasty\MegaMenu\Model\Provider\FieldsToHideProvider;
use Amasty\MegaMenuLite\Test\Unit\Traits;
use Amasty\MegaMenu\Ui\DataProvider\Form\Category\Modifier\HideItems;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\Store;

/**
 * Class SubcategoryTest
 * test Subcategory DataProvider
 *
 * @see Subcategory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubcategoryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Subcategory
     */
    private $model;

    protected function setup(): void
    {
        $getItemContentData = $this->createMock(GetItemContentData::class);
        $this->model = new Subcategory($getItemContentData);
    }


    /**
     * @covers Subcategory::isShowSubcategories
     *
     * @dataProvider isShowSubcategoriesDataProvider
     *
     * @throws \ReflectionException
     */
    public function testIsShowSubcategories(
        ?int $submenuType,
        ?int $subcategoriesPosition,
        int $entityId,
        int $level,
        int $storeId,
        bool $expectedResult
    ): void {
        $getItemContentData = $this->getProperty($this->model, 'getItemContentData');
        $getItemContentData->expects($this->any())->method('execute')->willReturnOnConsecutiveCalls($submenuType, $subcategoriesPosition);

        $category = $this->createMock(Category::class);
        $category->expects($this->any())->method('getEntityId')->willReturn($entityId);
        $category->expects($this->any())->method('getLevel')->willReturn($level);
        $category->expects($this->any())->method('getStoreId')->willReturn($storeId);

        $actualResult = $this->model->isShowSubcategories($category);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for isShowSubcategories test
     * @return array
     */
    public function isShowSubcategoriesDataProvider(): array
    {
        return [
            [
                SubmenuType::WITH_CONTENT,
                SubcategoriesPosition::LEFT,
                1,
                Subcategory::TOP_LEVEL,
                Store::DEFAULT_STORE_ID,
                true
            ],
            [
                SubmenuType::WITHOUT_CONTENT,
                SubcategoriesPosition::LEFT,
                1,
                Subcategory::TOP_LEVEL,
                Store::DEFAULT_STORE_ID,
                false
            ],
            [
                SubmenuType::WITHOUT_CONTENT,
                SubcategoriesPosition::LEFT,
                1,
                3,
                Store::DEFAULT_STORE_ID,
                true
            ],
            [
                SubmenuType::WITHOUT_CONTENT,
                SubcategoriesPosition::NOT_SHOW,
                1,
                3,
                Store::DEFAULT_STORE_ID,
                false
            ]
        ];
    }
}

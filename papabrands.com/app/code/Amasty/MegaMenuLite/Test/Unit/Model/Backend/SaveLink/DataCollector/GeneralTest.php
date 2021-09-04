<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Test\Unit\Model\Backend\SaveLink\DataCollector;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Backend\SaveLink\DataCollector\General;
use Amasty\MegaMenuLite\Model\OptionSource\UrlKey;
use Amasty\MegaMenuLite\Test\Unit\Traits;

/**
 * Class GeneralTest
 * test general data collector
 *
 * @see General
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneralTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Save::execute
     *
     * @dataProvider executeDataProvider
     *
     * @throws \ReflectionException
     */
    public function testExecute($data, $expectedResult)
    {
        $saveAction = $this->createPartialMock(General::class, []);

        $actualResult = $saveAction->execute($data);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for execute test
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                [
                    LinkInterface::ENTITY_ID => 1,
                    LinkInterface::TYPE => UrlKey::LINK,
                    LinkInterface::LINK => null
                ],
                [
                    LinkInterface::ENTITY_ID => 1,
                    LinkInterface::TYPE => UrlKey::NO,
                    LinkInterface::LINK => null
                ]
            ],
            [
                [
                    LinkInterface::ENTITY_ID => 0,
                    LinkInterface::TYPE => UrlKey::LINK,
                    LinkInterface::LINK => null
                ],
                [
                    LinkInterface::ENTITY_ID => 0,
                    LinkInterface::TYPE => UrlKey::NO,
                    LinkInterface::LINK => null
                ]
            ],
            [
                [
                    LinkInterface::ENTITY_ID => 1,
                    LinkInterface::TYPE => 3,
                    LinkInterface::LINK => 'test'
                ],
                [
                    LinkInterface::ENTITY_ID => 1,
                    LinkInterface::TYPE => 3,
                    LinkInterface::LINK => ''
                ]
            ],
            [
                [
                    LinkInterface::ENTITY_ID => 1,
                    LinkInterface::TYPE => UrlKey::LINK,
                    LinkInterface::LINK => 'test'
                ],
                [
                    LinkInterface::ENTITY_ID => 1,
                    LinkInterface::TYPE => UrlKey::LINK,
                    LinkInterface::LINK => 'test'
                ]
            ]
        ];
    }
}

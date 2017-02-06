<?php

declare(strict_types=1);

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Tests\Doctrine\Dbal;

use Doctrine\Common\Cache\Cache;
use Rollerworks\Component\Search\Doctrine\Dbal\CacheWhereBuilder;
use Rollerworks\Component\Search\Doctrine\Dbal\DoctrineDbalFactory;
use Rollerworks\Component\Search\Doctrine\Dbal\ValueConversionInterface;
use Rollerworks\Component\Search\Doctrine\Dbal\WhereBuilder;
use Rollerworks\Component\Search\Field\FieldConfig;
use Rollerworks\Component\Search\GenericFieldSet;
use Rollerworks\Component\Search\SearchCondition;
use Rollerworks\Component\Search\Value\ValuesGroup;

class DoctrineDbalFactoryTest extends DbalTestCase
{
    /**
     * @var DoctrineDbalFactory
     */
    protected $factory;

    public function testCreateWhereBuilder()
    {
        $connection = $this->getConnectionMock();
        $searchCondition = new SearchCondition(new GenericFieldSet([], 'invoice'), new ValuesGroup());

        $whereBuilder = $this->factory->createWhereBuilder($connection, $searchCondition);

        $this->assertInstanceOf(WhereBuilder::class, $whereBuilder);
    }

    public function testCreateWhereBuilderWithConversionSetting()
    {
        $conversion = $this->createMock(ValueConversionInterface::class);

        $fieldLabel = $this->getMockBuilder(FieldConfig::class)->getMock();
        $fieldLabel->expects($this->once())->method('hasOption')->with('doctrine_dbal_conversion')->will($this->returnValue(true));
        $fieldLabel->expects($this->once())->method('getOption')->with('doctrine_dbal_conversion')->will($this->returnValue($conversion));

        $fieldCustomer = $this->getMockBuilder(FieldConfig::class)->getMock();
        $fieldCustomer->expects($this->once())->method('hasOption')->with('doctrine_dbal_conversion')->will($this->returnValue(false));
        $fieldCustomer->expects($this->never())->method('getOption');

        $fieldSet = new GenericFieldSet(
            ['invoice_label' => $fieldLabel, 'invoice_customer' => $fieldCustomer],
            'invoice'
        );

        $connection = $this->getConnectionMock();
        $searchCondition = new SearchCondition($fieldSet, new ValuesGroup());

        $whereBuilder = $this->factory->createWhereBuilder($connection, $searchCondition);

        $this->assertInstanceOf(WhereBuilder::class, $whereBuilder);
    }

    public function testCreateWhereBuilderWithLazyConversionSetting()
    {
        $test = $this;
        $conversion = $test->getMockBuilder(ValueConversionInterface::class)->getMock();
        $lazyConversion = function () use ($conversion) {
            return $conversion;
        };

        $fieldLabel = $this->getMockBuilder(FieldConfig::class)->getMock();
        $fieldLabel->expects($this->once())->method('hasOption')->with('doctrine_dbal_conversion')->will($this->returnValue(true));
        $fieldLabel->expects($this->once())->method('getOption')->with('doctrine_dbal_conversion')->will($this->returnValue($lazyConversion));

        $fieldCustomer = $this->getMockBuilder(FieldConfig::class)->getMock();
        $fieldCustomer->expects($this->once())->method('hasOption')->with('doctrine_dbal_conversion')->will($this->returnValue(false));
        $fieldCustomer->expects($this->never())->method('getOption');

        $fieldSet = new GenericFieldSet(
            ['invoice_label' => $fieldLabel, 'invoice_customer' => $fieldCustomer],
            'invoice'
        );

        $connection = $this->getConnectionMock();
        $searchCondition = new SearchCondition($fieldSet, new ValuesGroup());

        $whereBuilder = $this->factory->createWhereBuilder($connection, $searchCondition);

        $this->assertInstanceOf(WhereBuilder::class, $whereBuilder);
    }

    public function testCreateCacheWhereBuilder()
    {
        $connection = $this->getConnectionMock();
        $searchCondition = new SearchCondition(new GenericFieldSet([], 'invoice'), new ValuesGroup());

        $whereBuilder = $this->factory->createWhereBuilder($connection, $searchCondition);
        $this->assertInstanceOf(WhereBuilder::class, $whereBuilder);

        $cacheWhereBuilder = $this->factory->createCacheWhereBuilder($whereBuilder);
        $this->assertInstanceOf(CacheWhereBuilder::class, $cacheWhereBuilder);
    }

    protected function setUp()
    {
        parent::setUp();

        $cacheDriver = $this->getMockBuilder(Cache::class)->getMock();
        $this->factory = new DoctrineDbalFactory($cacheDriver);
    }
}

<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;

/**
 * Class DbalDataProviderTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DbalDataProviderTest extends TestCase
{
	/**
	 * @dataProvider fieldMappingProvider
	 * @param QueryBuilder $queryBuilder
	 * @param array        $mapping
	 * @param              $column
	 * @param              $expectedMapping
	 */
	public function testFieldMapping(QueryBuilder $queryBuilder, array $mapping, $column, $expectedMapping)
	{
		$dataProvider = new DbalDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], $mapping));

		$columnMapping = $dataProvider->getFieldMapping($column);

		$this->assertEquals($expectedMapping, $columnMapping);
	}

	public function fieldMappingProvider()
	{
		$rootAlias = 'a';
		$qb = $this->getMockBuilder(QueryBuilder::class)
			->disableOriginalClone()
			->disableOriginalConstructor()
			->getMock();

		$qb
			->method('getQueryPart')
			->with('from')
			->willReturn([['alias' => $rootAlias], ['alias' => 'b']]);

		$mapping = [
			'test' => 'a.test',
		];

		return [
			[$qb, $mapping, 'test', 'a.test'],
			[$qb, $mapping, 'foo', $rootAlias.'.foo'],
		];
	}
}

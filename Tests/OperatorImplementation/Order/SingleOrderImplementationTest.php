<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Order;

use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Order\SingleOrderImplementation;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Order\SingleOrderDefinition;
use Kora\DataProvider\OperatorDefinition\OrderOperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class SingleOrderImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class SingleOrderImplementationTest extends TestCase
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
	 * @dataProvider applyProvider
	 * @param $orderColumn
	 * @param $orderColumnMapping
	 * @param $paramColumn
	 * @param $paramDirection
	 * @param $shouldOrder
	 */
	public function testApply($orderColumn, $orderColumnMapping, $paramColumn, $paramDirection, $shouldOrder)
	{
		$orderParamName = 'ord';
		$orderDirName = 'dir';

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		if($shouldOrder) {
			$queryBuilder
				->shouldReceive('addOrderBy')
				->with($orderColumnMapping, $paramDirection)
				->andReturnSelf()
				->once();
		} else {
			$queryBuilder
				->shouldNotReceive('addOrderBy');
		}


		$dataProvider = new DbalDataProvider($queryBuilder, new OperatorImplementationsList(), new Mapper([], [ $orderColumn => $orderColumnMapping ]));

		$orderDefinition = new SingleOrderDefinition([$orderColumn], $orderParamName, $orderDirName);
		$orderDefinition
			->initData([
				$orderParamName => $paramColumn,
				$orderDirName => $paramDirection
			]);

		$orderImplementation = new SingleOrderImplementation();
		$orderImplementation->apply($dataProvider, $orderDefinition);
	}

	public function applyProvider()
	{
		$orderColumn = 'test';
		$orderColumnMapping = 'test';

		return [
			[ $orderColumn, $orderColumnMapping, $orderColumn, OrderOperatorDefinitionInterface::DIR_DESC, true],
			[ $orderColumn, $orderColumnMapping, 'asdf', OrderOperatorDefinitionInterface::DIR_DESC, false]
		];
	}
}

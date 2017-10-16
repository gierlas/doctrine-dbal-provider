<?php

namespace Kora\DataProvider\Doctrine\Dbal\Tests\OperatorImplementation\Filter;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\EqualFilterImplementation;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\EqualFilterDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class EqualFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class EqualFilterImplementationTest extends TestCase
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	public function testIsWhereExecuted()
	{
		$paramName = 'test';
		$paramValue = 'asdf';
		$paramMapping = 'test';
		$sqlParam = ':' . $paramName;

		$expresionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expresionBuilder
			->shouldReceive('eq')
			->with($sqlParam, $paramMapping);

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('expr')
			->andReturn($expresionBuilder);

		$queryBuilder
			->shouldReceive('andWhere')
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParam, $paramValue)
			->once();

		$dataProvider = new DbalDataProvider(new OperatorImplementationsList(), $queryBuilder, new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new EqualFilterDefinition($paramName, true);
		$filterDefinition->initData([
			$paramName => $paramValue
		]);

		$filterImplementation = new EqualFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}

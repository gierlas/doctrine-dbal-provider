<?php

namespace Kora\DataProvider\Doctrine\Dbal\Tests\OperatorImplementation\Filter;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\DataProviderOperatorsSetup;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\Doctrine\Dbal\DbalImplementationList;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\RangeFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\Tests\AbstractDoctrineTest;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\RangeFilterDefinition;
use Mockery as m;

/**
 * Class RangeOperatorFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class RangeFilterImplementationTest extends AbstractDoctrineTest
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;


	/**
	 * @dataProvider resultProvider
	 * @param $min
	 * @param $max
	 * @param $filter
	 * @param $expectedCount
	 */
	public function testResult($min, $max, $filter, $expectedCount)
	{
		$em = $this->getPropagatedEM();
		$qb = $em->getConnection()->createQueryBuilder()
			->select('f.*')
			->from('foos', 'f');

		$setup = new DataProviderOperatorsSetup();
		$setup
			->addFilter(new RangeFilterDefinition('nbValue', $filter));

		$setup->setData([
			'nbValue' => [
				'min' => $min,
				'max' => $max
			]
		]);

		$ormDataProvider = new DbalDataProvider(clone $qb, new DbalImplementationList(), new Mapper());
		$data = $ormDataProvider->fetchData($setup);


		$this->assertEquals($expectedCount, $data->getNbAll());
		$this->assertCount($expectedCount, $data->getResults());
	}

	public function resultProvider()
	{
		return [
			[null, null, FILTER_VALIDATE_INT, count(AbstractDoctrineTest::getBasicFixtures())],
			[2, null, FILTER_VALIDATE_INT, 4],
			['2', '3', FILTER_VALIDATE_INT, 3],
			[6, null, FILTER_VALIDATE_INT, 0],
			[null, 0, FILTER_VALIDATE_INT, 0],
		];
	}

	public function testMinMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$min = 0;
		$max = 1;
		$sqlParamMin = ':' . $paramName . '_min';
		$sqlParamMax = ':' . $paramName . '_max';

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->with($paramMapping, $sqlParamMin)
			->once();

		$expressionBuilder
			->shouldReceive('lte')
			->with($paramMapping, $sqlParamMax)
			->once();

		$expressionBuilder
			->shouldReceive('andX')
			->once();

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('expr')
			->andReturn($expressionBuilder);

		$queryBuilder
			->shouldReceive('andWhere')
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParamMin, $min)
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParamMax, $max)
			->andReturnSelf()
			->once();

		$dataProvider = new DbalDataProvider($queryBuilder, new DbalImplementationList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new RangeFilterDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'min' => $min,
				'max' => $max
			]
		]);

		$filterImplementation = new RangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMin()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$min = 0;
		$sqlParam = ':' . $paramName;

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->with($paramMapping, $sqlParam)
			->once();

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('expr')
			->andReturn($expressionBuilder);

		$queryBuilder
			->shouldReceive('andWhere')
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParam, $min)
			->andReturnSelf()
			->once();

		$dataProvider = new DbalDataProvider($queryBuilder, new DbalImplementationList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new RangeFilterDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'min' => $min
			]
		]);

		$filterImplementation = new RangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$max = 0;
		$sqlParam = ':' . $paramName;

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('lte')
			->with($paramMapping, $sqlParam)
			->once();

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('expr')
			->andReturn($expressionBuilder);

		$queryBuilder
			->shouldReceive('andWhere')
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setParameter')
			->with($sqlParam, $max)
			->andReturnSelf()
			->once();

		$dataProvider = new DbalDataProvider($queryBuilder, new DbalImplementationList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new RangeFilterDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'max' => $max
			]
		]);

		$filterImplementation = new RangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}

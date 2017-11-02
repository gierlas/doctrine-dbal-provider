<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Filter;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\DataProviderOperatorsSetup;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\Doctrine\Dbal\DbalImplementationList;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\DateRangeFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\Tests\AbstractDoctrineTest;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\DateRangeDefinition;
use Mockery as m;

/**
 * Class DateRangeFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DateRangeFilterImplementationTest extends AbstractDoctrineTest
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
	 * @dataProvider resultProvider
	 * @param $min
	 * @param $max
	 * @param $format
	 * @param $hasTime
	 * @param $expectedCount
	 */
	public function testResult($min, $max, $format, $hasTime, $expectedCount)
	{
		$em = $this->getPropagatedEM();
		$qb = $em->getConnection()->createQueryBuilder()
			->select('f.*')
			->from('foos', 'f');

		$setup = new DataProviderOperatorsSetup();
		$setup
			->addFilter((new DateRangeDefinition('createdAt', $format))->setHasTimePart($hasTime));

		$setup->setData([
			'createdAt' => [
				'start' => $min,
				'end' => $max
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
			[null, null, 'Y-m-d', false, count(AbstractDoctrineTest::getBasicFixtures())],
			['2017-01-23', null, 'Y-m-d', false, 4],
			[null, '2017-01-23', 'Y-m-d', false, 3],
			['2017-01-21', '2017-01-22', 'Y-m-d', false, 2],
			[new \DateTime('2017-01-21'), new \DateTime('2017-01-22'), 'Y-m-d', false, 2],
			['2017-01-28', '2017-01-29', 'Y-m-d', false, 0],
			['2017-01-22 11:00:00', '2017-01-22 14:00:00', 'Y-m-d H:i:s', true, 1],
		];
	}

	public function testMin()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$date = new \DateTime('2017-01-01 12:22:21');

		$expectedDateMin = clone $date;
		$expectedDateMin->setTime(0, 0);


		$sqlParamMin = ':' . $paramName;

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->withArgs([$paramMapping, $sqlParamMin])
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
			->withArgs(function($paramName, $value) use($sqlParamMin, $expectedDateMin) {
				return ($paramName === $sqlParamMin && $value == $expectedDateMin);
			})
			->andReturnSelf();

		$dataProvider = new DbalDataProvider($queryBuilder, new DbalImplementationList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateRangeDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'start' => $date
			]
		]);

		$filterImplementation = new DateRangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$date = new \DateTime('2017-01-01 12:22:21');

		$expectedDateMax = clone $date;
		$expectedDateMax->setTime(0, 0)->modify('+1 day');


		$sqlParamMax = ':' . $paramName;

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('lt')
			->with($paramMapping, $sqlParamMax)
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
			->withArgs(function($paramName, $value) use($sqlParamMax, $expectedDateMax) {
				return ($paramName === $sqlParamMax && $value == $expectedDateMax);
			})
			->andReturnSelf();

		$dataProvider = new DbalDataProvider($queryBuilder, new DbalImplementationList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateRangeDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'end' => $date
			]
		]);

		$filterImplementation = new DateRangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMinMax()
	{
		$paramName = 'test';
		$paramMapping = 'test';
		$dateMin = new \DateTime('2017-01-01 12:22:21');
		$dateMax = new \DateTime('2017-01-02 12:22:21');

		$expectedDateMin = clone $dateMin;
		$expectedDateMin->setTime(0, 0);
		$expectedDateMax = clone $dateMax;
		$expectedDateMax->setTime(0, 0)->modify('+1 day');


		$sqlParamMin = ':' . $paramName . '_start';
		$sqlParamMax = ':' . $paramName . '_end';

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('gte')
			->with($paramMapping, $sqlParamMin)
			->once();

		$expressionBuilder
			->shouldReceive('lt')
			->with($paramMapping, $sqlParamMax)
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
			->withArgs(function($paramName, $value) use($sqlParamMin, $expectedDateMin, $sqlParamMax, $expectedDateMax) {
				return ($paramName === $sqlParamMin && $value == $expectedDateMin)
					|| ($paramName === $sqlParamMax && $value == $expectedDateMax);
			})
			->andReturnSelf();

		$dataProvider = new DbalDataProvider($queryBuilder, new DbalImplementationList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new DateRangeDefinition($paramName);
		$filterDefinition->initData([
			$paramName => [
				'start' => $dateMin,
				'end' => $dateMax
			]
		]);

		$filterImplementation = new DateRangeFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}

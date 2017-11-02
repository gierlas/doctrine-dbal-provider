<?php

namespace Kora\DataProvider\Doctrine\Dbal\Tests\OperatorImplementation\Filter;

use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\DataProviderOperatorsSetup;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\ChoiceFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\Doctrine\Dbal\DbalImplementationList;
use Kora\DataProvider\Doctrine\Dbal\Tests\AbstractDoctrineTest;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Filter\ChoiceFilter\ChoiceProvider\ArrayProvider;
use Kora\DataProvider\OperatorDefinition\Filter\ChoiceFilterDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use Mockery as m;

/**
 * Class ChoiceFilterImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class ChoiceFilterImplementationTest extends AbstractDoctrineTest
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
	 * @dataProvider resultProvider
	 * @param $term
	 * @param $choicesProvider
	 * @param $isMulti
	 * @param $expectedCount
	 */
	public function testResult($term, $choicesProvider, $isMulti, $expectedCount)
	{
		$em = $this->getPropagatedEM();
		$qb = $em->getConnection()->createQueryBuilder()
			->select('f.*')
			->from('foos', 'f');

		$setup = new DataProviderOperatorsSetup();
		$setup
			->addFilter(new ChoiceFilterDefinition('nbValue', $choicesProvider, $isMulti, FILTER_VALIDATE_INT));

		$setup->setData([
			'nbValue' => $term
		]);

		$ormDataProvider = new DbalDataProvider(clone $qb, new DbalImplementationList(), new Mapper());
		$data = $ormDataProvider->fetchData($setup);


		$this->assertEquals($expectedCount, $data->getNbAll());
		$this->assertCount($expectedCount, $data->getResults());
	}

	public function resultProvider()
	{
		return [
			[1, new ArrayProvider([1, 2, 3, 4]), false, 2],
			[2, new ArrayProvider([1, 2, 3, 4]), false, 1],
			[[1, 2], new ArrayProvider([1, 2, 3, 4]), true, 3],
			[4, new ArrayProvider([1, 2, 3, 4]), false, 0],
			[[1, 2, 4], new ArrayProvider([1, 2, 3, 4]), true, 3]
		];
	}

	public function testNotMulti()
	{
		$paramName = 'test';
		$paramValue = 'asdf';
		$paramMapping = 'test';
		$sqlParam = ':' . $paramName;

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('eq')
			->with($sqlParam, $paramMapping);

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
			->with($sqlParam, $paramValue)
			->once();

		$dataProvider = new DbalDataProvider($queryBuilder, new DbalImplementationList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new ChoiceFilterDefinition($paramName, new ArrayProvider([$paramValue]));
		$filterDefinition->initData([
			$paramName => $paramValue
		]);

		$filterImplementation = new ChoiceFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}

	public function testMulti()
	{
		$paramName = 'test';
		$paramValue = [ 'asdf' ];
		$paramMapping = 'test';
		$sqlParam = ':' . $paramName . '0';

		$expressionBuilder = m::mock(ExpressionBuilder::class)
			->shouldDeferMissing();

		$expressionBuilder
			->shouldReceive('in')
			->with($paramName, [$sqlParam]);

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
			->with($sqlParam, $paramValue[0])
			->once();

		$dataProvider = new DbalDataProvider($queryBuilder, new OperatorImplementationsList(), new Mapper([], [ $paramName => $paramMapping ]));

		$filterDefinition = new ChoiceFilterDefinition($paramName, new ArrayProvider(array_merge($paramValue, [])), true);
		$filterDefinition->initData([
			$paramName => $paramValue
		]);

		$filterImplementation = new ChoiceFilterImplementation();
		$filterImplementation->apply($dataProvider, $filterDefinition);
	}
}

<?php

namespace Kora\DataProvider\Doctrine\Orm\Tests\OperatorImplementation\Pager;

use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Pager\LimitOffsetPagerImplementation;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorDefinition\Pager\LimitOffsetPagerDefinition;
use Kora\DataProvider\OperatorImplementationsList;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * Class LimitOffsetPagerImplementationTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class LimitOffsetPagerImplementationTest extends TestCase
{
	use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

	public function testApply()
	{
		$offset = 10;
		$limit = 20;

		$queryBuilder = m::mock(QueryBuilder::class)
			->shouldDeferMissing();

		$queryBuilder
			->shouldReceive('setFirstResult')
			->with($offset)
			->andReturnSelf()
			->once();

		$queryBuilder
			->shouldReceive('setMaxResults')
			->with($limit)
			->andReturnSelf()
			->once();

		$dataProvider = new DbalDataProvider($queryBuilder, new OperatorImplementationsList(), new Mapper());

		$limitOffsetDefinition = new LimitOffsetPagerDefinition();
		$limitOffsetDefinition
			->initData([
				'_limit' => $limit,
				'_offset' => $offset
			]);

		$limitOffsetImplementation = new LimitOffsetPagerImplementation();
		$limitOffsetImplementation->apply($dataProvider, $limitOffsetDefinition);

	}
}

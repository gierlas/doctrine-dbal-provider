<?php

namespace Kora\DataProvider\Doctrine\Dbal;

use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\ChoiceFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\DateFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\DateRangeFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\EqualFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter\RangeFilterImplementation;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Order\SingleOrderImplementation;
use Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Pager\LimitOffsetPagerImplementation;
use Kora\DataProvider\OperatorDefinition\Filter\ChoiceFilterDefinition;
use Kora\DataProvider\OperatorDefinition\Filter\DateFilterDefinition;
use Kora\DataProvider\OperatorDefinition\Filter\DateRangeDefinition;
use Kora\DataProvider\OperatorDefinition\Filter\RangeFilterDefinition;
use Kora\DataProvider\OperatorImplementation\Filter\CallbackFilterImplementation;
use Kora\DataProvider\OperatorImplementationsList;
use Kora\DataProvider\OperatorDefinition\Filter\CallbackFilterDefinition;
use Kora\DataProvider\OperatorDefinition\Filter\EqualFilterDefinition;
use Kora\DataProvider\OperatorDefinition\Order\SingleOrderDefinition;
use Kora\DataProvider\OperatorDefinition\Pager\LimitOffsetPagerDefinition;


/**
 * Class DbalImplementationList
 * @author Paweł Gierlasiński <gierlasinski.pawel@gmail.com>
 */
class DbalImplementationList extends OperatorImplementationsList
{
	/**
	 * OrmImplementationList constructor.
	 */
	public function __construct()
	{
		$this->initOperators();
	}

	/**
	 * Init base operators
	 */
	protected function initOperators()
	{
		$this
			->addImplementation(
				EqualFilterDefinition::class, new EqualFilterImplementation()
			)
			->addImplementation(
				ChoiceFilterDefinition::class, new ChoiceFilterImplementation()
			)
			->addImplementation(
				RangeFilterDefinition::class, new RangeFilterImplementation()
			)
			->addImplementation(
				DateFilterDefinition::class, new DateFilterImplementation()
			)
			->addImplementation(
				DateRangeDefinition::class, new DateRangeFilterImplementation()
			)
			->addImplementation(
				CallbackFilterDefinition::class, new CallbackFilterImplementation()
			)
			->addImplementation(
				SingleOrderDefinition::class, new SingleOrderImplementation()
			)
			->addImplementation(
				LimitOffsetPagerDefinition::class, new LimitOffsetPagerImplementation()
			);
	}
}
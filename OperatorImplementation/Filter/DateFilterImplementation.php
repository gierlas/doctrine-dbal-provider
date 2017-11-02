<?php

namespace Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter;

use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\DateFilterDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class DateFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DateFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return DateFilterDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var DbalDataProvider     $dataProvider
		 * @var DateFilterDefinition $definition
		 */

		if ($definition->getDate() === null) return;

		$field = $dataProvider->getFieldMapping($definition->getName());

		if ($definition->hasDatePart() && !$definition->hasTimePart()) {
			$this->handleDate($field, $dataProvider, $definition);
			return;
		}

		$qb = $dataProvider->getQueryBuilder();
		$param = ':' . $definition->getName();

		$format = $definition->hasDatePart() ? 'Y-m-d H:i:s' : 'H:i:s';

		$qb
			->andWhere($qb->expr()->eq($field, $param))
			->setParameter($param, $definition->getDate()->format($format));
	}

	/**
	 * @param                      $field
	 * @param DbalDataProvider     $dataProvider
	 * @param DateFilterDefinition $definition
	 */
	protected function handleDate($field, DbalDataProvider $dataProvider, DateFilterDefinition $definition)
	{

		$qb = $dataProvider->getQueryBuilder();
		$paramStart = ':' . $definition->getName() . '_start';
		$paramEnd = ':' . $definition->getName() . '_end';

		$startTime = $definition->getDate()->setTime(0, 0);
		$endTime = clone $definition->getDate();
		$endTime->modify('+1 day');

		$qb
			->andWhere(
				$qb->expr()->andX(
					$qb->expr()->gte($field, $paramStart),
					$qb->expr()->lt($field, $paramEnd)
				)
			)
			->setParameter($paramEnd, $endTime->format('Y-m-d'))
			->setParameter($paramStart, $startTime->format('Y-m-d'));
	}

}
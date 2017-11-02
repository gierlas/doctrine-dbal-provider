<?php

namespace Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter;

use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\DateRangeDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class DateRangeFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class DateRangeFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @var string
	 */
	protected $format;

	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return DateRangeDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var DbalDataProvider    $dataProvider
		 * @var DateRangeDefinition $definition
		 */
		$fieldName = $dataProvider->getFieldMapping($definition->getName());
		$dateStart = $this->prepareDate($definition->getDateStart(), $definition, true);
		$dateEnd = $this->prepareDate($definition->getDateEnd(), $definition, false);
		$this->determineFormat($definition);

		if ($dateStart !== null && $dateEnd !== null) {
			$this->handleBoth($fieldName, $dataProvider, $definition, $dateStart, $dateEnd);
			return;
		}

		$qb = $dataProvider->getQueryBuilder();
		$paramName = ':' . $definition->getName();


		$this->applyDate($qb, $fieldName, $paramName, $this->getComparisonType(true, $definition->hasTimePart()), $dateStart);
		$this->applyDate($qb, $fieldName, $paramName, $this->getComparisonType(false, $definition->hasTimePart()), $dateEnd);
	}

	protected function determineFormat(DateRangeDefinition $definition)
	{
		$dateFormat = $definition->hasDatePart() ? 'Y-m-d' : '';
		$timeFormat = $definition->hasTimePart() ? 'H:i:s' : '';

		$this->format = trim($dateFormat . ' ' . $timeFormat);
	}

	/**
	 * @param \DateTime|null      $date
	 * @param DateRangeDefinition $definition
	 * @param bool                $isStart
	 * @return null|\DateTime
	 */
	protected function prepareDate($date, DateRangeDefinition $definition, bool $isStart)
	{
		if ($date === null) {
			return null;
		}

		$retDate = clone $date;

		if (!$definition->hasTimePart()) {
			$retDate->setTime(0, 0);

			if (!$isStart) {
				$retDate->modify('+1 day');
			}
		}

		return $retDate;
	}

	/**
	 * @param string              $field
	 * @param DbalDataProvider    $dataProvider
	 * @param DateRangeDefinition $definition
	 * @param \DateTime           $dateStart
	 * @param \DateTime           $dateEnd
	 */
	protected function handleBoth(
		string $field, DbalDataProvider $dataProvider, DateRangeDefinition $definition,
		\DateTime $dateStart, \DateTime $dateEnd
	)
	{
		$qb = $dataProvider->getQueryBuilder();
		$paramStart = ':' . $definition->getName() . '_start';
		$paramEnd = ':' . $definition->getName() . '_end';

		$qb
			->andWhere(
				$qb->expr()->andX(
					$qb->expr()->gte($field, $paramStart),
					$qb->expr()->lt($field, $paramEnd)
				)
			)
			->setParameter($paramStart, $dateStart->format($this->format))
			->setParameter($paramEnd, $dateEnd->format($this->format));
	}

	/**
	 * @param QueryBuilder $qb
	 * @param string       $fieldName
	 * @param string       $paramName
	 * @param string       $type
	 * @param              $date
	 */
	protected function applyDate(QueryBuilder $qb, string $fieldName, string $paramName, string $type, $date)
	{
		if ($date === null) {
			return;
		}

		$qb
			->andWhere($qb->expr()->{$type}($fieldName, $paramName))
			->setParameter($paramName, $date->format($this->format));
	}

	/**
	 * @param bool $isStart
	 * @param bool $hasTime
	 * @return string
	 */
	protected function getComparisonType(bool $isStart, bool $hasTime): string
	{
		return $isStart ? 'gte' : ($hasTime ? 'lte' : 'lt');
	}
}
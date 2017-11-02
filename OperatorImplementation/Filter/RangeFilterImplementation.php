<?php

namespace Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter;

use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\RangeFilterDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class RangeOperatorFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class RangeFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return RangeFilterDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var DbalDataProvider      $dataProvider
		 * @var RangeFilterDefinition $definition
		 */
		$fieldName = $dataProvider->getFieldMapping($definition->getName());

		if ($definition->getMin() !== null && $definition->getMax() !== null) {
			$this->handleMinMax($fieldName, $dataProvider, $definition);
			return;
		}

		$qb = $dataProvider->getQueryBuilder();
		$paramName = ':' . $definition->getName();

		$this->applyComparison($qb, $fieldName, $paramName, 'gte', $definition->getMin());
		$this->applyComparison($qb, $fieldName, $paramName, 'lte', $definition->getMax());
	}

	/**
	 * @param string                $field
	 * @param DbalDataProvider      $dataProvider
	 * @param RangeFilterDefinition $definition
	 */
	protected function handleMinMax(string $field, DbalDataProvider $dataProvider, RangeFilterDefinition $definition)
	{
		$qb = $dataProvider->getQueryBuilder();
		$paramStart = ':' . $definition->getName() . '_min';
		$paramEnd = ':' . $definition->getName() . '_max';

		$qb
			->andWhere(
				$qb->expr()->andX(
					$qb->expr()->gte($field, $paramStart),
					$qb->expr()->lte($field, $paramEnd)
				)
			)
			->setParameter($paramStart, $definition->getMin())
			->setParameter($paramEnd, $definition->getMax());
	}


	/**
	 * @param QueryBuilder $qb
	 * @param string       $fieldName
	 * @param string       $paramName
	 * @param string       $type
	 * @param              $value
	 */
	public function applyComparison(QueryBuilder $qb, string $fieldName, string $paramName, string $type, $value)
	{
		if ($value === null) {
			return;
		}

		$qb
			->andWhere($qb->expr()->{$type}($fieldName, $paramName))
			->setParameter($paramName, $value);
	}
}
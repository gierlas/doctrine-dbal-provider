<?php

namespace Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Filter;

use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\OperatorDefinition\Filter\ChoiceFilterDefinition;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class ChoiceFilterImplementation
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
class ChoiceFilterImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return ChoiceFilterDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/**
		 * @var DbalDataProvider       $dataProvider
		 * @var ChoiceFilterDefinition $definition
		 */
		if (empty($definition->getValue())) return;

		$qb = $dataProvider->getQueryBuilder();
		$field = $dataProvider->getFieldMapping($definition->getName());
		$param = ':' . $definition->getName();

		if ($definition->isMulti() && is_array($definition->getValue())) {
			$this->handleMulti($qb, $definition, $field);
		} else {
			$qb
				->andWhere($qb->expr()->eq($field, $param))
				->setParameter($param, $definition->getValue());
		}
	}

	/**
	 * @param QueryBuilder           $qb
	 * @param ChoiceFilterDefinition $definition
	 * @param string                 $field
	 */
	protected function handleMulti(QueryBuilder $qb, ChoiceFilterDefinition $definition, string $field)
	{
		$values = $definition->getValue();
		$nameCallback = function ($id) use($definition) { return ':' . $definition->getName() . $id; };

		$in = array_map(function($id) use ($nameCallback) { return $nameCallback($id); }, array_keys($values));

		$qb->andWhere($qb->expr()->in($field, $in));

		foreach ($definition->getValue() as $id => $value) {
			$qb->setParameter($nameCallback($id), $value);
		}
	}
}
<?php

namespace Kora\DataProvider\Doctrine\Dbal\OperatorImplementation\Order;

use Kora\DataProvider\Doctrine\Dbal\DbalDataProvider;
use Kora\DataProvider\DataProviderInterface;
use Kora\DataProvider\OperatorDefinition\Order\SingleOrderDefinition;
use Kora\DataProvider\OperatorDefinition\OrderOperatorDefinitionInterface;
use Kora\DataProvider\OperatorDefinitionInterface;
use Kora\DataProvider\OperatorImplementationInterface;


/**
 * Class SingleOrderImplementation
 * @author Paweł Gierlasiński <gierlasinski.pawel@gmail.com>
 */
class SingleOrderImplementation implements OperatorImplementationInterface
{
	/**
	 * @return string
	 */
	public function getOperatorDefinitionCode(): string
	{
		return SingleOrderDefinition::class;
	}

	/**
	 * @param DataProviderInterface       $dataProvider
	 * @param OperatorDefinitionInterface $definition
	 */
	public function apply(DataProviderInterface $dataProvider, OperatorDefinitionInterface $definition)
	{
		/** @var DbalDataProvider $dataProvider */
		/** @var SingleOrderDefinition $definition */

		if (empty($definition->getColumnName())) return;

		$qb = $dataProvider->getQueryBuilder();

		//@TODO Param is provided by frontend, implement some other mapping?
		$field = $dataProvider->getFieldMapping($definition->getColumnName());
		$order = $this->getOrder($definition);

		$qb
			->addOrderBy($field, $order);
	}

	/**
	 * @param SingleOrderDefinition $definition
	 * @return string
	 */
	protected function getOrder(SingleOrderDefinition $definition): string
	{
		return $definition->getDirection() === OrderOperatorDefinitionInterface::DIR_ASC
			? OrderOperatorDefinitionInterface::DIR_ASC : OrderOperatorDefinitionInterface::DIR_DESC;
	}
}
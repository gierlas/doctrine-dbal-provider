<?php

namespace Kora\DataProvider\Doctrine\Dbal;

use Doctrine\DBAL\Query\QueryBuilder;
use Kora\DataProvider\AbstractDataProvider;
use Kora\DataProvider\Mapper;
use Kora\DataProvider\OperatorImplementationsList;


/**
 * Class DbalDataProvider
 * @author Paweł Gierlasiński <gierlasinski.pawel@gmail.com>
 */
class DbalDataProvider extends AbstractDataProvider
{
	/**
	 * @var QueryBuilder
	 */
	private $queryBuilder;

	/**
	 * DbalDataProvider constructor.
	 * @param QueryBuilder                $queryBuilder
	 * @param OperatorImplementationsList $implementationsList
	 * @param Mapper                      $mapper
	 */
	public function __construct(QueryBuilder $queryBuilder, OperatorImplementationsList $implementationsList, Mapper $mapper)
	{
		parent::__construct($implementationsList, $mapper);
		$this->queryBuilder = $queryBuilder;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getFieldMapping(string $name): string
	{
		$map = $this->mapper->getOperatorFieldMap();
		if (isset($map[$name])) return $map[$name];

		$from = $this->queryBuilder->getQueryPart('from') ?? [];
		$mainAlias = $from[0]['alias'] ?? [];

		if (empty($mainAlias)) return $name;

		return $mainAlias . '.' . $name;
	}

	/**
	 * @return array
	 */
	public function fetchFromDataSource(): array
	{
		$stmt = $this->queryBuilder->execute();
		return $stmt->fetchAll();
	}

	/**
	 * Little dirty, maybe there's better way. It should also handle GROUP BY scenario
	 * @return int
	 */
	public function count(): int
	{
		$limit = $this->queryBuilder->getMaxResults();
		$offset = $this->queryBuilder->getFirstResult();

		$this->queryBuilder->setMaxResults(null);
		$this->queryBuilder->setFirstResult(null);

		$sql = $this->queryBuilder->getSQL();

		$this->queryBuilder->setMaxResults($limit);
		$this->queryBuilder->setFirstResult($offset);

		$stmt = $this->queryBuilder->getConnection()->prepare("
			SELECT COUNT(*) 
			FROM ($sql) AS counter
		");


		foreach ($this->queryBuilder->getParameters() as $name => $value) {
			$stmt->bindValue($name, $value, $this->queryBuilder->getParameterType($name));
		}

		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * @return QueryBuilder
	 */
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}
}
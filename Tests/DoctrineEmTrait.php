<?php

namespace Kora\DataProvider\Doctrine\Dbal\Tests;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;


/**
 * Trait DoctrineEmTrait
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
trait DoctrineEmTrait
{
	public function getDoctrineAnnotationConfig(array $paths)
	{
		return Setup::createAnnotationMetadataConfiguration($paths, true, null, null, false);
	}

	public function getEntityManager(Configuration $configuration, EventManager $eventManager = null)
	{
		return EntityManager::create([
			'driver' => 'pdo_sqlite',
			'memory' => true,
		], $configuration, $eventManager);
	}

	public function initEntityManager(EntityManager $em)
	{
		$schemaTool = new SchemaTool($em);
		$schemaTool->getUpdateSchemaSql($em->getMetadataFactory()->getAllMetadata());
		$schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
	}

	public function getInitializedEntityManager(array $paths, EventManager $eventManager = null)
	{
		$config = $this->getDoctrineAnnotationConfig($paths);
		$em = $this->getEntityManager($config, $eventManager);
		$this->initEntityManager($em);

		return $em;
	}
}
<?php

namespace Kora\DataProvider\Doctrine\Dbal\Tests;

use Doctrine\ORM\EntityManager;
use Kora\DataProvider\Doctrine\Dbal\Tests\Fixtures\Foo;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractDoctrineTest
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 */
abstract class AbstractDoctrineTest extends TestCase
{
	use DoctrineEmTrait;

	const FIXTURES_PATH = __DIR__ . '/Fixtures/';

	/**
	 * @return EntityManager
	 */
	protected function prepareEntityManager(): EntityManager
	{
		$em = $this->getInitializedEntityManager([self::FIXTURES_PATH]);
		$em->getConfiguration()->addEntityNamespace('Test', 'Kora\\DataProvider\\Doctrine\\Dbal\\Tests\\Fixtures');
		return $em;
	}

	/**
	 * @param EntityManager $em
	 * @param array         $fixtures
	 */
	protected function propagateData(EntityManager $em, array $fixtures)
	{
		foreach ($fixtures as $fixture) {
			$foo = Foo::create(...$fixture);
			$em->persist($foo);
		}

		$em->flush();
	}

	/**
	 * @return array
	 */
	public static function getBasicFixtures(): array
	{
		return [
			['Title1', 1, \DateTime::createFromFormat('H:i:s', "10:22:00"), new \DateTime('2017-01-22 10:15:00')],
			['Title1', 3, \DateTime::createFromFormat('H:i:s', "14:33:50"), new \DateTime('2017-01-22 13:45:00')],
			['Title2', 2, \DateTime::createFromFormat('H:i:s', "15:33:00"), new \DateTime('2017-01-23 12:15:00')],
			['Title3', 3, \DateTime::createFromFormat('H:i:s', "17:33:00"), new \DateTime('2017-01-24 13:14:00')],
			['Title4', 1, \DateTime::createFromFormat('H:i:s', "11:33:00"), new \DateTime('2017-01-25 11:00:00')],
			['Title4', 5, \DateTime::createFromFormat('H:i:s', "11:33:00"), new \DateTime('2017-01-26 11:00:00')],
		];
	}

	/**
	 * @param array $fixtures
	 * @return EntityManager
	 */
	public function getPropagatedEM(array $fixtures = []): EntityManager
	{
		$fixtures = empty($fixtures) ? self::getBasicFixtures() : $fixtures;
		$em = $this->prepareEntityManager();
		$this->propagateData($em, $fixtures);

		return $em;
	}
}
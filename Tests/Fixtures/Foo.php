<?php

namespace Kora\DataProvider\Doctrine\Dbal\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;


/**
 * Class Foo
 * @author Paweł Gierlasiński <pawel@mediamonks.com>
 * @ORM\Entity()
 * @ORM\Table(name="foos")
 */
class Foo
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $title;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $nbValue;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="time")
	 */
	protected $time;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $createdAt;

	public static function create(string $title, int $nbValue, \DateTime $time, \DateTime $createdAt): Foo
	{
		$foo = new Foo();
		$foo
			->setTitle($title)
			->setNbValue($nbValue)
			->setTime($time)
			->setCreatedAt($createdAt);

		return $foo;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Foo
	 */
	public function setId(int $id): Foo
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return Foo
	 */
	public function setTitle(string $title): Foo
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getNbValue(): int
	{
		return $this->nbValue;
	}

	/**
	 * @param int $nbValue
	 * @return Foo
	 */
	public function setNbValue(int $nbValue): Foo
	{
		$this->nbValue = $nbValue;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getTime(): \DateTime
	{
		return $this->time;
	}

	/**
	 * @param \DateTime $time
	 * @return Foo
	 */
	public function setTime(\DateTime $time): Foo
	{
		$this->time = $time;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt(): \DateTime
	{
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt
	 * @return Foo
	 */
	public function setCreatedAt(\DateTime $createdAt): Foo
	{
		$this->createdAt = $createdAt;
		return $this;
	}


}
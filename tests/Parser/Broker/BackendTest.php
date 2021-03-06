<?php

namespace ApiGen\Parser\Tests\Broker;

use ApiGen\Contracts\Parser\Configuration\ParserConfigurationInterface;
use ApiGen\Contracts\Parser\ParserStorageInterface;
use ApiGen\Contracts\Parser\Reflection\ClassReflectionInterface;
use ApiGen\Contracts\Parser\Reflection\FunctionReflectionInterface;
use ApiGen\Contracts\Parser\Reflection\TokenReflection\ReflectionFactoryInterface;
use ApiGen\Parser\Broker\Backend;
use ApiGen\Parser\Reflection\TokenReflection\ReflectionFactory;
use Mockery;
use PHPUnit_Framework_Assert;
use PHPUnit_Framework_TestCase;
use TokenReflection\Broker;


class BackendTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var BackendInterface
	 */
	private $backend;

	/**
	 * @var Broker
	 */
	private $broker;


	protected function setUp()
	{
		$this->backend = new Backend($this->getReflectionFactory());
		$this->broker = new Broker($this->backend);
	}


	public function testGetClasses()
	{
		$this->broker->processDirectory(__DIR__ . '/BackendSource');
		$classes = $this->backend->getClasses();
		$this->assertCount(1, $classes);

		$class = array_pop($classes);
		$this->assertInstanceOf(ClassReflectionInterface::class, $class);

		$this->checkLoadedProperties($class);
	}


	public function testGetFunctions()
	{
		$this->broker->processDirectory(__DIR__ . '/BackendSource');
		$functions = $this->backend->getFunctions();
		$this->assertCount(1, $functions);

		$function = array_pop($functions);
		$this->assertInstanceOf(FunctionReflectionInterface::class, $function);

		$this->checkLoadedProperties($function);
	}


	public function testGetConstants()
	{
		$this->broker->processDirectory(__DIR__ . '/BackendSource');
		$constants = $this->backend->getConstants();
		$this->assertCount(1, $constants);

		$constant = array_pop($constants);
		$this->assertInstanceOf('ApiGen\Parser\Reflection\ReflectionConstant', $constant);

		$this->checkLoadedProperties($constant);
	}


	/**
	 * @param object $object
	 */
	private function checkLoadedProperties($object)
	{
		$this->assertInstanceOf(
			ParserConfigurationInterface::class,
			PHPUnit_Framework_Assert::getObjectAttribute($object, 'configuration')
		);

		$this->assertInstanceOf(
			ParserStorageInterface::class,
			PHPUnit_Framework_Assert::getObjectAttribute($object, 'parserResult')
		);

		$this->assertInstanceOf(
			ReflectionFactoryInterface::class,
			PHPUnit_Framework_Assert::getObjectAttribute($object, 'reflectionFactory')
		);
	}


	/**
	 * @return ReflectionFactory
	 */
	private function getReflectionFactory()
	{
		$parserStoragetMock = Mockery::mock(ParserStorageInterface::class);
		$parserConfigurationMock = Mockery::mock(ParserConfigurationInterface::class);
		$parserConfigurationMock->shouldReceive('isPhpCoreDocumented')->andReturn(TRUE);
		$parserConfigurationMock->shouldReceive('isInternalDocumented')->andReturn(TRUE);
		$parserConfigurationMock->shouldReceive('getVisibilityLevel')->andReturn(1);
		return new ReflectionFactory($parserConfigurationMock, $parserStoragetMock);
	}

}

<?php
namespace Passbin\Base\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */
use Passbin\Base\Domain\Model\Pass;
use Passbin\Base\Tests\FactoryService;

include_once __DIR__."/../../../FactoryService.php";

/**
 * Testcase for Pass
 */
class PassTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var FactoryService
	 */
	protected $factoryService;

	protected $passRepoMock;

	public function setUp() {
		parent::setUp();

		$this->factoryService = new FactoryService();

		$passRepoMock = $this->getMock('\Passbin\Base\Domain\Repository\PassRepository', array("update"));
		$passRepoMock->expects($this->any())->method("update")->will($this->returnValue("false"));
		$this->passRepoMock = $passRepoMock;
	}

	/**
	 * @test
	 */
	public function makeSureThatCreaterCanBeSet() {
		$creator = "marcel";

		$pass = $this->factoryService->createPass(array("creator" => $creator));

		$this->assertSame($creator, $pass->getCreator());
	}

	/**
	 * @test
	 */
	public function aPassReturnsIfItIsValidOrNot() {
		$pass = $this->factoryService->createPass();
		$this->assertNotNull($pass->isValid());
	}

	/**
	 * Als valid makiert wenn callable > 0 ist
	 * @test
	 */
	public function aPassIsOnlyValidIfItHasStillCallablesLeft() {
		$pass = $this->factoryService->createPass(array("expiration" => new \DateTime('tomorrow'), "callable" => 0));
		$this->inject($pass, "passRepository", $this->passRepoMock);

		$this->assertFalse($pass->isValid());

		$pass->setCallable(-1);
		$this->assertFalse($pass->isValid());

		$pass->setCallable(3);
		$this->assertTrue($pass->isValid());
	}

	/**
	 * ExpirationDate > now
	 * @test
	 */
	public function aPassIsOnlyValidIfTheExpirationDateIsInTheFuture() {
		$pass = $this->factoryService->createPass(array("callable" => 3, "expiration" => new \DateTime('yesterday')));
		$this->inject($pass, "passRepository", $this->passRepoMock);

		$this->assertFalse($pass->isValid());

		$date = new \DateTime('now');
		$pass->setExpiration($date);
		$this->assertFalse($pass->isValid(), "Test for <= now");

	}

	/**
	 * @todo
	 */
	public function ifAPassIsNotValidThePasswordAndTheSecureIsSetToNull() {
		$foo = "bar";
		$pass = $this->factoryService->createPass(array("password" => $foo, "secure" => $foo));
		$this->inject($pass, "passRepository", $this->passRepoMock);

		$this->assertSame($foo, $pass->getPassword());
		$this->assertSame($foo, $pass->getSecure());
		$this->assertTrue($pass->isValid());

		$this->assertNull($pass->getPassword(), "ads");
		$this->assertNull($pass->getSecure(), "dsa");
	}
}
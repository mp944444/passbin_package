<?php
namespace Passbin\Base\Tests\Unit\Domain;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */
use Passbin\Base\Domain\Model\Pass;
use Passbin\Base\Domain\Model\User;
use Passbin\Base\Tests\FactoryService;

include_once __DIR__."/../FactoryService.php";

/**
 * Class FactoryServiceTest
 * @package Passbin\Base\Tests\Unit\Domain
 */
class FactoryServiceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 *
	 * @var FactoryService $factoryService
	 */
	protected $factoryService;

	public function setUp() {
		parent::setUp();

		$this->factoryService = new FactoryService();
	}


	/**
	 * @test
	 */
	public function checkIfPassPropertyIDCanBeSet() {
		$id = "myID";
		$pass = $this->factoryService->createPass(array("id" => $id));

		$this->assertSame($id, $pass->getId());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyCreatorCanBeSet() {
		$creator = "CreatorName";
		$pass = $this->factoryService->createPass(array("creator" => $creator));

		$this->assertSame($creator, $pass->getCreator());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyHeadlineCanBeSet() {
		$headline = "myHeadline";
		$pass = $this->factoryService->createPass(array("headline" => $headline));

		$this->assertSame($headline, $pass->getHeadline());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertySendEmailCanBeSet() {
		$sendEmail = "yes";
		$pass = $this->factoryService->createPass(array("sendEmail" => $sendEmail));

		$this->assertSame($sendEmail, $pass->getSendEmail());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyEmailCanBeSet() {
		$email = "your@email.de";
		$pass = $this->factoryService->createPass(array("email" => $email));

		$this->assertSame($email, $pass->getEmail());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyPasswordCanBeSet() {
		$password = "YourPassword";
		$pass = $this->factoryService->createPass(array("password" => $password));

		$this->assertSame($password, $pass->getPassword());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertySecureCanBeSet() {
		$secure = "YourSecure";
		$pass = $this->factoryService->createPass(array("secure" => $secure));

		$this->assertSame($secure, $pass->getSecure());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyCreationDateCanBeSet() {
		$creationDate = new \DateTime('now');
		$pass = $this->factoryService->createPass(array("creationDate" => $creationDate));

		$this->assertSame($creationDate, $pass->getCreationDate());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyExpirationCanBeSet() {
		$expiration = new \DateTime('tomorrow');
		$pass = $this->factoryService->createPass(array("expiration" => $expiration));

		$this->assertSame($expiration, $pass->getExpiration());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyCallableCanBeSet() {
		$callable = 5;
		$pass = $this->factoryService->createPass(array("callable" => $callable));

		$this->assertSame($callable, $pass->getCallable());
	}

	/**
	 * @test
	 */
	public function checkIfPassPropertyUserCanBeSet() {
		/**
		 * @var User $user
		 */
		$user = $this->factoryService->createUser();
		$pass = $this->factoryService->createPass(array("user" => $user));

		$this->assertSame($user, $pass->getUser());
	}
}
<?php

namespace Passbin\Base\Tests;
use Passbin\Base\Domain\Model\Pass;
use Passbin\Base\Domain\Model\User;

/**
 * Created by PhpStorm.
 * User: Marcel P
 * Date: 19.12.2014
 * Time: 11:12
 */

class FactoryService {

	public function createUser(array $properties = array()) {
		$properties = array_merge(
			array(
				"firstname"	=> "yourFirstname",
				"lastname" => "yourLastname",
				"lastlogin" => new \DateTime('yesterday'),
				"email" => "yourEmail",
				"resetid" => "yourResetId"
			),
			$properties
		);

		$user = new User();
		$user->setFirstname($properties['firstname']);
		$user->setLastname($properties['lastname']);
		$user->setLastLogin($properties['lastlogin']);
		$user->setEmail($properties['email']);
		$user->setResetid($properties['resetid']);

		return $user;
	}

	public function createPass(array $properties = array()) {
		$properties = array_merge(
			array(
				"id" 			=> "passid",
				"creator" 		=> "foo creator",
				"headline" 		=> "Dummy Pass",
				"sendEmail"		=> "",
				"email"			=> "foo@bar.org",
				"password"		=> "password",
				"secure"		=> "secure foo",
				"creationDate" 	=> new \DateTime('now'),
				"expiration" 	=> new \DateTime('tomorrow'),
				"callable" 		=> 3,
				"user" 			=> $this->createUser()
			),
			$properties
		);

		$pass = new Pass();
		$pass->setId($properties['id']);
		$pass->setCreator($properties['creator']);
		$pass->setHeadline($properties['headline']);
		$pass->setSendEmail($properties['sendEmail']);
		$pass->setEmail($properties['email']);
		$pass->setPassword($properties['password']);
		$pass->setSecure($properties['secure']);
		$pass->setCreationDate($properties['creationDate']);
		$pass->setExpiration($properties['expiration']);
		$pass->setCallable($properties['callable']);

		return $pass;
	}
}
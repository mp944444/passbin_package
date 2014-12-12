<?php
namespace Passbin\Base\Domain\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("session")
 */
class UserStorage {
	/**
	 * @var string $username
	 */
	protected $username;

	/**
	 * @param string $user
	 * @return void
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function addUser($user = "") {
		$this->username = $user;
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->username;
	}
}
<?php


namespace Passbin\Base\Domain\Service;
use Passbin\Base\Domain\Model\User;
use Passbin\Base\Domain\Model\Pass;
use \TYPO3\Flow\Security\AccountRepository;
use \Passbin\Base\Domain\Repository\UserRepository;
/**
 * Class NoteReadService
 * @package Passbin\Base\Domain\Service
 */
class NoteReadService {
	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @var \Passbin\Base\Domain\Repository\UserRepository
	 */
	protected $userRepository;


	/**
	 * @param string $username
	 * @return array
	 */
	public function readUserNotes($username) {
		$ar = new AccountRepository();
		$ur = new UserRepository();

		/** @var User $user */
		$account = $ar->findByAccountIdentifierAndAuthenticationProviderName($username, "DefaultProvider");

		$user = $ur->findOneByAccount($account);

		$entrys = array();

		foreach($user->getPassEntrys() as $entry) {
			$entrys[] = array(
				"headline" => $entry->getHeadline(),
				"creationdate" => $entry->getCreationDate()->format('d.m.Y H:i:s'),
				"expiration" => $entry->getExpiration()->format('d.m.Y H:i:s'),
				"callable" => $entry->getCallable(),
				"id" => $entry->getId()
			);
		}

		return $entrys;
	}
} 
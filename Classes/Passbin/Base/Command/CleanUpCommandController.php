<?php
namespace Passbin\Base\Command;

use Passbin\Base\Domain\Model\Pass;
use TYPO3\Flow\Annotations as Flow;
/**
 * Class CleanUpController
 * @package Passbin\Base\Command
 */
class CleanUpCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \Passbin\Base\Domain\Repository\PassRepository
	 * @Flow\Inject
	 */
	protected $passRepository;

	/**
	 * @var \Passbin\Base\Domain\Repository\UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;


	/**
	 * Delete old notes
	 *
	 * @param string $date all entries which are created before this date will be deleted
	 * @param bool $mustExpired must entry be expired? [TRUE/FALSE]
	 * @param bool $userNotes delete notes from registered users? [TRUE/FALSE]
	 * @param bool $nonUserNotes delete notes from unregistered users? [TRUE/FALSE]
	 */
	public function deleteOldNotesCommand($date, $mustExpired = TRUE, $userNotes = FALSE, $nonUserNotes = TRUE) {
		$count = 0;
		if($userNotes == FALSE && $nonUserNotes == FALSE) {
			$entries = NULL;
			$this->outputLine("userNotes and nonUserNotes can not both be FALSE");
		} else if($userNotes == TRUE && $nonUserNotes == FALSE) {
			$entries = $this->passRepository->findAllByUser(TRUE);
		} else if($userNotes == FALSE && $nonUserNotes == TRUE) {
			$entries = $this->passRepository->findAllByUser(FALSE);
		} else {
			$entries = $this->passRepository->findAll();
		}

		if($entries != NULL) {
			foreach($entries as $entry) {
				/** @var Pass $entry */
				if($mustExpired && $entry->getExpiration() < new \DateTime('now') && $entry->getCreationDate() < new \DateTime($date) || $mustExpired && $entry->getCallable() == 0) {
					$this->passRepository->remove($entry);
					$count++;
				} else if($mustExpired == FALSE && $entry->getCreationDate() < new \DateTime($date)) {
					$this->passRepository->remove($entry);
					$count++;
				}
			}
		}
		$this->outputLine("There were ".$count." note(s) deleted");
	}

	/**
	 * Delete inactive Users
	 *
	 * @param string $lastLogin all users who have not logged in after this date will be deleted
	 * @param boolean $activatedAccounts Activated accounts will be deleted too [TRUE/FALSE]
	 */
	public function deleteInactiveUserCommand($lastLogin, $activatedAccounts = FALSE) {
		$count = 0;
		$entrycount = 0;
		$users = $this->userRepository->findInactiveUsers($lastLogin);

		foreach($users as $user) {
			/** @var \Passbin\Base\Domain\Model\User $user
			 * @var \TYPO3\Flow\Security\Account $account
			 */
			if($user->isActivated() && $activatedAccounts) {
				$this->userRepository->remove($user);
				$this->accountRepository->remove($user->getAccount());

				$entries = $this->passRepository->findAllByUser($user);
				foreach($entries as $entry) {
					$this->passRepository->remove($entry);
					$entrycount++;
				}
				$count++;
			} else if(!$user->isActivated()) {
				$this->userRepository->remove($user);
				$this->accountRepository->remove($user->getAccount());

				$entries = $this->passRepository->findAllByUser($user);
				foreach($entries as $entry) {
					$this->passRepository->remove($entry);
					$entrycount++;
				}
				$count++;
			}
		}
		$this->outputLine("There where ".$count." users and ".$entrycount." notes deleted");
	}
}
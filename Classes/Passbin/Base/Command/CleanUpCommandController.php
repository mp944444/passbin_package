<?php
namespace Passbin\Base\Command;

use Passbin\Base\Domain\Model\Pass;
use Passbin\Base\Domain\Model\User;
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

	/**
	 * Notes and Users Overview
	 */
	public function statisticCommand() {
		$passStatistic = array(
			"Generally" => array(
				"category" => "Generally",
				"Notes" => 0,
				"Active Notes" => 0,
				"Expired Notes" => 0
			),
			"User statistic" => array(
				"category" => "User statistic",
				"Notes" => 0,
				"Active Notes" => 0,
				"Expired Notes" => 0
			),
			"Non User statistic" => array(
				"category" => "Non User statistic",
				"Notes" => 0,
				"Active Notes" => 0,
				"Expired Notes" => 0
			)
		);

		$userStatistic = array(
			"accounts" => 0,
			"activated" => 0,
			"non activated" => 0
		);

		foreach($this->userRepository->findAll() as $user) {
			/** @var User $user */
			$userStatistic["accounts"] = $userStatistic["accounts"]+1;
			if($user->isActivated()) {
				$userStatistic["activated"] = $userStatistic["activated"]+1;
			} else {
				$userStatistic["non activated"] = $userStatistic["non activated"]+1;
			}
		}


		foreach($this->passRepository->findAll() as $pass) {
			/** @var Pass $pass */
			$passStatistic["Generally"]["Notes"] = $passStatistic["Generally"]["Notes"]+1;
			if($pass->getCallable() > 0 && $pass->getExpiration()->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')) {
				$passStatistic["Generally"]["Active Notes"] = $passStatistic["Generally"]["Active Notes"]+1;
			} else {
				$passStatistic["Generally"]["Expired Notes"] = $passStatistic["Generally"]["Expired Notes"]+1;
			}

			if($pass->getUser() != NULL) {
				$passStatistic["User statistic"]["Notes"] = $passStatistic["User statistic"]["Notes"]+1;
				if($pass->getCallable() > 0 && $pass->getExpiration()->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')) {
					$passStatistic["User statistic"]["Active Notes"] = $passStatistic["User statistic"]["Active Notes"]+1;
				} else {
					$passStatistic["User statistic"]["Expired Notes"] = $passStatistic["User statistic"]["Expired Notes"]+1;
				}
			} else {
					$passStatistic["Non User statistic"]["Notes"] = $passStatistic["Non User statistic"]["Notes"]+1;
					if($pass->getCallable() > 0 && $pass->getExpiration()->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')) {
						$passStatistic["Non User statistic"]["Active Notes"] = $passStatistic["Non User statistic"]["Active Notes"]+1;
					} else {
						$passStatistic["Non User statistic"]["Expired Notes"] = $passStatistic["Non User statistic"]["Expired Notes"]+1;
					}
			}
		}

		$this->outputLine("");
		$this->outputLine("Pass Statistic");
		foreach($passStatistic as $stat) {
			$this->outputLine("");
			$this->outputLine($stat['category']);
			$this->outputLine("-------------------------------------------");
			next($stat);
			$this->outputLine(key($stat)."                ".$stat[key($stat)]);
			next($stat);
			$this->outputLine(key($stat)."         ".$stat[key($stat)]);
			next($stat);
			$this->outputLine(key($stat)."        ".$stat[key($stat)]);
			$this->outputLine("-------------------------------------------");
		}
		$this->outputLine("");
		$this->outputLine("");
		$this->outputLine("-------------------------------------------");
		$this->outputLine("User Statistic");
		$this->outputLine("-------------------------------------------");
		$this->outputLine("Accounts             ".$userStatistic["accounts"]);
		$this->outputLine("Activated            ".$userStatistic["activated"]);
		$this->outputLine("Non Activated        ".$userStatistic["non activated"]);
		$this->outputLine("-------------------------------------------");
		$this->outputLine("");
	}
}
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
	 * Delete old notes
	 *
	 * @param string $date all previous entries will be deleted >2014-12-18<
	 * @param bool $mustExpired must entry be expired? TRUE/FALSE
	 * @param bool $userNotes delete notes from registered users? TRUE/FALSE
	 * @param bool $nonUserNotes delete notes from unregistered users? TRUE/FALSE
	 */
	public function deleteOldNotesCommand($date, $mustExpired = TRUE, $userNotes = FALSE, $nonUserNotes = TRUE) {

		if($userNotes == FALSE && $nonUserNotes == FALSE) {
			$entries = NULL;
			$this->outputLine("userNotes and nonUserNotes can be not FALSE");
		} else if($userNotes == TRUE && $nonUserNotes == FALSE) {
			$entries = $this->passRepository->findAllByUser(TRUE);
		} else if($userNotes == FALSE && $nonUserNotes == TRUE) {
			$entries = $this->passRepository->findAllByUser(FALSE);
		} else {
			$entries = $this->passRepository->findAll();
		}

		foreach($entries as $entry) {
			/** @var Pass $entry */
			if($mustExpired && $entry->getExpiration() < new \DateTime('now') && $entry->getCreationDate() < new \DateTime($date)) {
				$this->outputLine($entry->getHeadline());
				$this->passRepository->remove($entry);
			} else if($mustExpired == FALSE && $entry->getCreationDate() < new \DateTime($date)) {
				$this->outputLine($entry->getHeadline());
				$this->passRepository->remove($entry);
			}
		}
	}
}
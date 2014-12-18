<?php
namespace Passbin\Base\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class PassRepository extends Repository {

	/**
	 * @param boolean $userNotes
	 * @return array
	 */
	public function findAllByUser($userNotes) {
		$query = $this->createQuery();
		if($userNotes) {
			$query->matching(
				$query->logicalNot(
					$query->equals("user", Null)
				)
			);
		} else {
			$query->matching(
				$query->equals("user", NULL)
			);
		}
		return $query->execute();
	}
}
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
class UserRepository extends Repository {

	/**
	 * @param string $lastLogin
	 * @return array
	 */
	public function findInactiveUsers($lastLogin) {
		$date = new \DateTime($lastLogin);

		$query = $this->createQuery();
		$query->matching(
			$query->lessThan("lastLogin", $date)
		);
		return $query->execute();
	}
}
<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class BaseController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @FLow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	public function initializeAction() {
		if(!$this->authenticationManager->isAuthenticated()) {
			$this->addFlashMessage("Please log in first!", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("start", "User");
		}
	}
}
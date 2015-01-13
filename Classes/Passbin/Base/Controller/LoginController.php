<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */
use Passbin\Base\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;

class LoginController extends \TYPO3\Flow\Mvc\Controller\ActionController {
	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * @var \Passbin\Base\Domain\Repository\UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var \Passbin\Base\Domain\Service\AccountService
	 * @FLow\Inject
	 */
	protected $accountService;

	/**
	 * @return void
	 */
	public function logoutAction() {
		$this->authenticationManager->logout();
		$this->addFlashMessage("Erfolgreich abgemeldet", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
		$this->redirect("new", "createPass");
	}

	/**
	 * @throws \Exception
	 */
	public function authenticateAction() {
		$check = false;
		try{
			$this->authenticationManager->authenticate();
			if ($this->authenticationManager->isAuthenticated()) {
				$check = true;
			}
		} catch(\Exception $e){
			$this->addFlashMessage("Username und / oder Passwort ist falsch!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
		}
		if($check === true) {
			/** @var User $user */
			$user = $this->accountService->getActiveAuthenticatedUser();
			if($user->isActivated()) {
				$user->setLastLogin(new \DateTime('now'));
				$this->userRepository->update($user);
				$this->addFlashMessage("Erfolgreich angemeldet!", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
				$this->redirect("new", "CreatePass");
			} else {
				$username = $user->getAccount()->getAccountIdentifier();
				$this->authenticationManager->logout();
				$this->addFlashMessage("Bitte aktiviere erst Account aktivieren!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR, array("username" => $username), 5365);
				$this->redirect("start", "User");
			}
		} else {
			$this->redirect("start", "User");
		}
	}
}
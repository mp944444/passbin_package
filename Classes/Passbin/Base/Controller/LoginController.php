<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */
use Passbin\Base\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;

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
	 * @return void
	 */
	public function logoutAction() {
		$this->authenticationManager->logout();
		$this->addFlashMessage("You've been logged out", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
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
			$this->addFlashMessage("Username and / or password is wrong!", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
		}
		if($check === true) {
			/** @var User $user */
			$account = $this->authenticationManager->getSecurityContext()->getAccount();
			$user = $this->userRepository->findOneByAccount($account);
			$user->setLastLogin(new \DateTime('now'));
			$this->userRepository->update($user);
			$this->addFlashMessage("Successfully logged in", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
			$this->redirect("new", "CreatePass");
		} else {
			$this->redirect("start", "User");
		}
	}
}
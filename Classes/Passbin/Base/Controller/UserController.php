<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use Passbin\Base\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;

class UserController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\Flow\Security\AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var \Passbin\Base\Domain\Repository\UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;


	/**
	 * @return void
	 */
	public function startAction() {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("new", "CreatePass");
		}
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
		} catch (\Exception $e){
			$this->addFlashMessage("Username and / or password is wrong!", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
		}

		if($check === true) {
			$this->redirect("new", "CreatePass");
		} else {
			$this->redirect("start", "User");
		}
	}

	/**
	 * @param string $firstname
	 * @param string $lastname
	 * @return void
	 */
	public function registerAction($firstname = "", $lastname = "") {
		$this->view->assignMultiple(array(
			"firstname" => $firstname,
			"lastname" => $lastname
		));
	}

	/**
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $username
	 * @param string $password
	 */
	public function createAccountAction($firstname, $lastname, $username, $password) {

		if($this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, "DefaultProvider" )) {
			$this->addFlashMessage("Name is not available", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("register", "User", NULL, $settings = array(
				"firstname" => $firstname,
				"lastname" => $lastname
			));
		} else {

			$user = new User();
			$user->setFirstname($firstname);
			$user->setLastname($lastname);

			$account = $this->accountFactory->createAccountWithPassword($username, $password);

			$user->setAccount($account);

			$this->userRepository->add($user);
			$this->accountRepository->add($account);

			$this->addFlashMessage("Account successfully created!", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
			$this->redirect("start", "User");
		}
	}

}
<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use Passbin\Base\Domain\Model\User;
use Passbin\Base\Domain\Model\Pass;
use Passbin\Base\Domain\Service\UserStorage;
use Passbin\Base\Domain\Service\NoteReadService;
use TYPO3\Flow\Annotations as Flow;

class UserController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\Flow\Security\AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * @var UserStorage
	 * @Flow\Inject
	 */
	protected $userStorage;

	/**
	 * @var NoteReadService
	 * @Flow\Inject
	 */
	protected $noteReadService;

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
	 * @var \Passbin\Base\Domain\Repository\PassRepository
	 * @Flow\Inject
	 */
	protected $passRepository;

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
	 * @return void
	 */
	public function logoutAction() {
		$this->authenticationManager->logout();
		$this->addFlashMessage("You've been logout", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
		$this->redirect("start", "User");
	}

	/**
	 * @throws \Exception
	 * @param string $username
	 */
	public function authenticateAction($username = "") {
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

			$this->userStorage->setUser($username);

			$this->addFlashMessage("Successfully logged in", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);

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
<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use Passbin\Base\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;

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
	public function startAction() {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("new", "CreatePass");
		}
	}

	/**
	 * @param string $username
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $email
	 * @return void
	 */
	public function registerAction($username = "", $firstname = "", $lastname = "", $email = "") {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("new", "createPass");
		}
		$this->view->assignMultiple(array(
			"username" => $username,
			"firstname" => $firstname,
			"lastname" => $lastname,
			"email" => $email
		));
	}

	/**
	 * @param string $firstname
	 * @param string $email
	 * @param string $lastname
	 * @param string $username
	 * @param string $password
	 */
	public function createAccountAction($firstname, $lastname, $username, $password, $email) {
		if(strlen($password) < 8) {
			$this->addFlashMessage("Password minimum length are 8 characters", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("register", "User", NULL, array(
				"firstname" => $firstname,
				"lastname" => $lastname,
				"username" => $username,
				"email" => $email
			));
		} else if($firstname == "" || $lastname == "" || $email == "" || $username == "") {
			$this->addFlashMessage("Please fill all fields", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("register", "User", NULL, array(
				"firstname" => $firstname,
				"lastname" => $lastname,
				"username" => $username,
				"email" => $email
			));
		} else if($this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, "DefaultProvider" )) {
			$this->addFlashMessage("Name is not available", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("register", "User", NULL, array(
				"firstname" => $firstname,
				"lastname" => $lastname,
				"email" => $email
			));
		} else {
			$user = $this->userRepository->findOneByEmail($email);
			if($user != NULL) {
				$this->addFlashMessage("Account with this email exists", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
				$this->redirect("register", "User", NULL, array(
					"firstname" => $firstname,
					"lastname" => $lastname,
					"username" => $username
				));
			}

			$emailValidator = new \TYPO3\Flow\Validation\Validator\EmailAddressValidator();
			$emailvalid = $emailValidator->validate($email);
			$notEmptyValidator = new \TYPO3\Flow\Validation\Validator\NotEmptyValidator();
			$notemptyvalid = $notEmptyValidator->validate($email);
			if ($notemptyvalid->hasErrors() || $emailvalid->hasErrors()) {
				$this->addFlashMessage("E-Mail is not valid", "", Message::SEVERITY_ERROR);
				$this->redirect("register", "User", NULL, array(
					"firstname" => $firstname,
					"lastname" => $lastname,
					"username" => $username
				));
			}

			$account = $this->accountFactory->createAccountWithPassword($username, $password);

			$user = new User();
			$user->setLastLogin(new \DateTime('now'));
			$user->setEmail($email);
			$user->setResetid("");
			$user->setFirstname($firstname);
			$user->setLastname($lastname);
			$user->setActivated(false);
			$user->setAccount($account);
			$this->userRepository->add($user);
			$this->accountRepository->add($account);

			$mail = new \TYPO3\SwiftMailer\Message();
			$mail->setFrom(array('noreply@passb.in' => 'Passbin'))
				 ->setTo(array($user->getEmail() => ''))
				 ->setSubject("Welcome to Passbin")
				 ->setBody('Welcome to Passbin. Please click on the following link to activate your account. '.$this->request->getHttpRequest()->getBaseUri().'activate/'.$username)
				 ->send();
			$this->addFlashMessage("Account successfully created!", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
			$this->redirect("start", "User");
		}
	}

	/**
	 * @return void
	 */
	public function resetPwAction() {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("start", "User");
		}
	}

	/**
	 * @param string $username
	 * @return void
	 */
	public function sendResetMailAction($username = "") {
		/** @var  \TYPO3\Flow\Security\Account $account
		 * @var User $user */
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("start", "User");
		}
		if($username == "" || $this->accountService->getAccount($username) == NULL) {
			$this->addFlashMessage("Please enter your username", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("resetpw", "User");
		}
		$user = $this->accountService->getActiveUser($username);
		$date = explode('-', date('H-i-s-m-d-Y'));
		$resetid = mktime($date[0],$date[1],$date[2],$date[3],$date[4],$date[5]);
		$user->setResetid($resetid);
		$this->userRepository->update($user);
		$mail = new \TYPO3\SwiftMailer\Message();
		$mail->setFrom(array('noreply@passb.in ' => 'Passbin'))
			->setTo(array($user->getEmail() => ''))
			->setSubject("Password reset for ".$username)
			->setBody('If you want to change your password please click here: '.$this->request->getHttpRequest()->getBaseUri().'reset/'.$resetid.'. If you do not ordered a password change do nothing. The link will expire automatically in 1 hour.')
			->send();
		$this->addFlashMessage("An Email with further instructions has been sent");
		$this->redirect("start", "User");
	}

	/**
	 * @param string $id
	 * @return void
	 */
	public function newPasswordAction($id) {
		/** @var User $user */
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("start", "User");
		}
		$user = $this->userRepository->findOneByResetid($id);
		if($user == NULL) {
			$this->addFlashMessage("Invalid link", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("start", "User");
		}
		$iddate = new \DateTime(date("Y-m-d H:i:s", $user->getResetid()));
		$actualdate = new \DateTime('-1 hour');

		if($actualdate > $iddate) {
			$this->addFlashMessage("Your reset link is expired", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("start", "User");
		}
		if($user === NULL){
			$this->addFlashMessage("Invalid id", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("start", "User");
		}
		$this->view->assignMultiple(array(
			"id" => $id
		));
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @return void
	 */
	public function changePasswordAction($username, $password, $id) {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("start", "User");
		}
		/** @var User $user */
		$user = $this->accountService->getActiveUser($username);
		if($user != NULL && $password != NULL && $user->getResetid() == $id) {
			$this->accountService->setPassword($this->accountService->getAccount($username),$password);
			$user->setResetid("");
			$this->userRepository->update($user);
			$this->addFlashMessage("Your Password has been changed");
			$this->redirect("start", "User");
		} else {
			$this->addFlashMessage("Please fill all fields", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("newPassword", "User", NULL, array(
				"id" => $id
			));
		}
	}

	/**
	 * @param string $username
	 */
	public function activateAccountAction($username) {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("start", "User");
		}
		/** @var User $user */
		$user = $this->accountService->getActiveUser($username);
		if($user->isActivated()) {
			$this->addFlashMessage("User is already active! You can login", "", Message::SEVERITY_NOTICE);
		} else {
			$this->addFlashMessage("User has been activated You can now login");
			$user->setActivated(true);
			$this->userRepository->update($user);
		}
		$this->redirect("start", "User");
	}
}
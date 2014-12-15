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
	 * @param string $firstname
	 * @param string $lastname
	 * @return void
	 */
	public function registerAction($firstname = "", $lastname = "") {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("new", "createPass");
		}
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
	$captcha = $_POST['g-recaptcha-response'];
	$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Le0Sf8SAAAAAN8K5IbEmosTGwdPCYHn_zE9ykqc&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
	$check = strpos($response, "true");

		if($check == false) {
			$this->addFlashMessage("Please check the captcha first", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("register", "User", NULL, array(
				"firstname" => $firstname,
				"lastname" => $lastname
			));
		} else if($this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, "DefaultProvider" )) {
				$this->addFlashMessage("Name is not available", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
				$this->redirect("register", "User", NULL, array(
					"firstname" => $firstname,
					"lastname" => $lastname
				));
			} else {
				$user = new User();
				$user->setLastLogin(new \DateTime(date("Y-m-d H:i:s")));
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
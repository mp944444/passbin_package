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
     * @var \Passbin\Base\Domain\Service\CaptchaService
     * @FLow\Inject
     */
    protected $captchaService;

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
        $publicKey = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.publicKey");

        $this->view->assignMultiple(array(
            "username" => $username,
            "firstname" => $firstname,
            "lastname" => $lastname,
            "email" => $email,
            "publicKey"  => $publicKey
        ));
	}

	/**
	 * @param string $firstname
	 * @param string $email
	 * @param string $lastname
	 * @param string $username
	 * @param string $password
	 * @param string $confirmPassword
	 */
	public function createAccountAction($firstname, $lastname, $username, $password, $email, $confirmPassword) {
        $privateKey = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.privateKey");

        if($this->captchaService->verifyCaptcha($_POST['g-recaptcha-response'], $privateKey)) {
            if(strlen($password) < 8 || $password != $confirmPassword) {
                $this->addFlashMessage("Passwörter stimmen nicht überein oder ist zu kurz (mindestens 8 Zeichen)!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
                $this->redirect("register", "User", NULL, array(
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "username" => $username,
                    "email" => $email
                ));
            } else if($firstname == "" || $lastname == "" || $email == "" || $username == "") {
                $this->addFlashMessage("Bitte alle Felder ausfüllen!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
                $this->redirect("register", "User", NULL, array(
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "username" => $username,
                    "email" => $email
                ));
            } else if($this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, "DefaultProvider" )) {
                $this->addFlashMessage("Username bereits vergeben!!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
                $this->redirect("register", "User", NULL, array(
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "email" => $email
                ));
            } else {
                $user = $this->userRepository->findOneByEmail($email);
                if($user != NULL) {
                    $this->addFlashMessage("Ein Account mit der Email Adresse ist bereits registriert!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
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
                    $this->addFlashMessage("Email ist nicht gültig!", "", Message::SEVERITY_ERROR);
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
                    ->setSubject("Willkommen bei Passbin")
                    ->setBody('Willkommen bei Passbin. Bitte auf den folgenden Link klicken um den Account zu aktivieren. '.$this->request->getHttpRequest()->getBaseUri().'activate/'.$username)
                    ->send();

                $this->addFlashMessage("Account wurde erstellt! Bitte auf den Link in der Email klicken", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
                $this->redirect("start", "User");
            }
        } else {
            $this->addFlashMessage("Captcha konnte nicht verifiziert werden.", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
            $this->redirect("register", "User", "Passbin.Base");
        }
	}

	/**
	 * @return void
	 */
	public function resetPwAction() {
		if($this->authenticationManager->isAuthenticated()) {
			$this->redirect("start", "User");
		} else {
            $publicKey = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.publicKey");
            $this->view->assignMultiple(array(
               "publicKey" => $publicKey,
            ));
        }
	}

	/**
	 * @param string $username
	 * @return void
	 */
	public function sendResetMailAction($username = "") {
        $privateKey = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.privateKey");

        if($this->captchaService->verifyCaptcha($_POST['g-recaptcha-response'], $privateKey)) {
            /** @var  \TYPO3\Flow\Security\Account $account
             * @var User $user */
            if($this->authenticationManager->isAuthenticated()) {
                $this->redirect("start", "User");
            }
            if($username == "" || $this->accountService->getAccount($username) == NULL) {
                $this->addFlashMessage("Bitte Username eingeben!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
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
                ->setSubject("Passwort ändern von ".$username)
                ->setBody('Wenn das Passwort geändert werden soll, bitte hier klicken: '.$this->request->getHttpRequest()->getBaseUri().'reset/'.$resetid.'. Wenn keine Passwort Änderung nötig ist bitte diese Email ignorieren. Der Link wird in einer Stunde automatisch ungültig.')
                ->send();

            $this->addFlashMessage("Eine Email mit weiteren Anweisungen wurde gesendet!");
            $this->redirect("start", "User");
        } else {
            $this->addFlashMessage("Captcha konnte nicht verifiziert werden.", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
            $this->redirect("resetPw", "User", "Passbin.Base");
        }
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
			$this->addFlashMessage("Ungültiger Link!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("start", "User");
		}

		$iddate = new \DateTime(date("Y-m-d H:i:s", $user->getResetid()));
		$actualdate = new \DateTime('-1 hour');

		if($actualdate > $iddate) {
			$this->addFlashMessage("Der Link ist nicht mehr gültig!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("start", "User");
		}

		if($user === NULL){
			$this->addFlashMessage("Ungültige Id!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
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

		if($user != NULL && strlen($password) >= 8 && $user->getResetid() == $id) {
			$this->accountService->setPassword($this->accountService->getAccount($username),$password);
			$user->setResetid("");
			$this->userRepository->update($user);
			$this->addFlashMessage("Das Passwort wurde erfolgreich geändert!");
			$this->redirect("start", "User");
		} else {
			$this->addFlashMessage("Bitte alle Felder vollständig ausfüllen!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
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
			$this->addFlashMessage("User ist bereits aktiviert!", "", \TYPO3\Flow\Error\Message::SEVERITY_NOTICE);
		} else {
			$this->addFlashMessage("User wurde aktiviert!");
			$user->setActivated(true);
			$this->userRepository->update($user);
		}

		$this->redirect("start", "User");
	}

	/**
	 * @param string $username
	 * @return void
	 */
	public function sendActivationMailAgainAction($username) {
		/**
		 * @var User $user
		 */
		$user = $this->accountService->getActiveUser($username);

		$mail = new \TYPO3\SwiftMailer\Message();
		$mail->setFrom(array('noreply@passb.in' => 'Passbin'))
			->setTo(array($user->getEmail() => ''))
			->setSubject("Aktivierung")
			->setBody('Bitte auf den folgenden Link klicken um den Account zu aktivieren. '.$this->request->getHttpRequest()->getBaseUri().'activate/'.$username)
			->send();

		$this->addFlashMessage("Eine Email wurde gesendet!", "", \TYPO3\Flow\Error\Message::SEVERITY_OK);
		$this->redirect("start", "User");
	}

	/**
	 * @return void
	 */
	public function getUsernameAction() {}

	/**
	 * @param string $email
	 * @return void
	 */
	public function sentUsernameAction($email = "") {
		$emailValidator = new \TYPO3\Flow\Validation\Validator\EmailAddressValidator();
		$emailvalid = $emailValidator->validate($email);
		$notEmptyValidator = new \TYPO3\Flow\Validation\Validator\NotEmptyValidator();
		$notemptyvalid = $notEmptyValidator->validate($email);
		if ($notemptyvalid->hasErrors() || $emailvalid->hasErrors()) {
			$this->addFlashMessage("Die Email ist nicht gültig");
			$this->redirect("getUsername", "User");
		}

		/** @var User $user */
		$user = $this->userRepository->findOneByEmail($email);

		if($user != NULL) {
			$username = $user->getAccount()->getAccountIdentifier();

			$mail = new \TYPO3\SwiftMailer\Message();
			$mail->setFrom(array('noreply@passb.in' => 'Passbin'))
				->setTo(array($user->getEmail() => ''))
				->setSubject("Account bei Passbin")
				->setBody('Username: '.$username.'.')
				->send();

			$this->addFlashMessage("Eine Email mit dem Benutzernamen wurde verschickt!");
			$this->redirect("start", "User");
		} else {
			$this->addFlashMessage("Es existiert kein Benutzer mit dieser Email", "", Message::SEVERITY_ERROR);
			$this->redirect("getUsername", "User");
		}
	}
}
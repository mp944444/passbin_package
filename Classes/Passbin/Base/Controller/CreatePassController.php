<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use Passbin\Base\Domain\Model\Pass;
use Passbin\Base\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;

class CreatePassController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
	 * @var \Passbin\Base\Domain\Repository\PassRepository
	 * @Flow\Inject
	 */
	protected $passRepository;

	/**
	 * @var ConfigurationManager
	 * @Flow\Inject
	 */
	protected $configurationManager;

    /**
	 * @todo loginstatus auf true/false nicht 0/1
	 * @param string $headline
	 * @param int $callable
	 * @param string $expiration
	 * @param string $email
     * @return void
     */
    public function newAction($headline = "", $callable = 0, $expiration = "", $email = "") {
		// @todo ifAuthenticatedViewHelper nutzen
		if($this->authenticationManager->isAuthenticated()) {
			$loginStatus = 0;
		} else {
			$loginStatus = 1;
		}


		$callableOptions = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.callableOptions");
		$this->view->assignMultiple(array(
			"headline" => $headline,
			"expiration" => $expiration,
			"callable" => $callable,
			"email" => $email,
			"login" => $loginStatus,
			"callableOptions" => $callableOptions
		));
	}

	/**
	 *
	 * @todo umbennen in listNotes
	 * @return void
	 */
	public function listNotesAction() {
		if(!$this->authenticationManager->isAuthenticated()) {
			$this->addFlashMessage("Please login to view your notes !", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("new", "CreatePass");
		}
		/** @var User $user */
		$account = $this->authenticationManager->getSecurityContext()->getAccount();
		$user = $this->userRepository->findOneByAccount($account);

		$this->view->assignMultiple(array(
			"entrys" => $user->getActiveEntries(),
			"expired" => $user->getExpiredEntries()
		));
	}

	/**
	* @return void
	* @param string $passId
	*/
    public function generateLinkAction($passId) {
		$link = $this->request->getHttpRequest()->getBaseUri()."id/".$passId;

		// @todo ifAuthenticatedViewHelper nutzen
		$login = 0;
		if($this->authenticationManager->isAuthenticated()) {
			$login = 1;
		}
		$this->view->assignMultiple(array(
			"link" => $link,
			"login" => $login
		));
	}

	/**
	* @return void
	* @param \Passbin\Base\Domain\Model\Pass $newPass
	* @Flow\Validate(argumentName="newPass.secure", type="NotEmpty")
	* @Flow\Validate(argumentName="newPass.password", type="StringLength", options={"minimum"=5,"maximum"=100})
	* @Flow\Validate(argumentName="newPass", type="\Passbin\Base\Validator\PassSendMailValidator")
	* @param string $expiration
	* @param string $callable
	*/
	public function createAction(\Passbin\Base\Domain\Model\Pass $newPass, $expiration, $callable = NULL) {
		$callableOptions = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.callableOptions");

		// Check expiration date
		// @todo \DateTime nutzen
		if($expiration == "") {
			$expiration = date('Y-m-d H:i:s', strtotime('1 hour'));
		} else {
			$expiration = date('Y-m-d H:i:s', strtotime($expiration));
			if($expiration <= date('Y-m-d H:i:s')) {
				$this->addFlashMessage("Expiration Date is expired", "Error!", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
				$this->redirect("new", "CreatePass");
			}
		}

		$account = $this->authenticationManager->getSecurityContext()->getAccount();
		$newPass->setUser($this->userRepository->findOneByAccount($account));
		$newPass->setExpiration(new \DateTime($expiration));
		if($callable == NULL) {
			$newPass->setCallable($callableOptions[0]);
		} else {
			$newPass->setCallable($callableOptions[$callable]);
		}

		$newPass->setId(uniqid());
		$newPass->setSecure(\Passbin\Base\Domain\Service\CryptionService::encryptData($newPass->getSecure()));
		$newPass->setPassword(\Passbin\Base\Domain\Service\CryptionService::encryptData($newPass->getPassword()));
		$newPass->setCreator($this->request->getHttpRequest()->getClientIpAddress());
		$newPass->setCreationDate(new \DateTime("now"));
		$this->passRepository->add($newPass);

		if ($newPass->getSendEmail() === "yes") {
			$name = "Someone";
			if($newPass->getUser() != NULL) {
				$name = $newPass->getUser()->getFirstname().' '.$newPass->getUser()->getLastname();
			}
			$mail = new \TYPO3\SwiftMailer\Message();
			$mail->setFrom(array('noreply@passb.in ' => 'Passbin'))
				->setTo(array($newPass->getEmail() => ''))
				->setSubject($name.' shared a secure Note with you!')
				->setBody('New secure Note for you. Here: '.$this->request->getHttpRequest()->getBaseUri()."id/".$newPass->getId().' /// You can encrypt this note '.$newPass->getCallable().' time(s) until: '.$newPass->getExpiration()->format("Y-m-d H:i")." /// Please use the following password to encrypt: ".\Passbin\Base\Domain\Service\CryptionService::decryptData($newPass->getPassword()))
				->send();
		}
		$this->redirect("generateLink", "CreatePass", "Passbin.Base", array("passId" => $newPass->getId()));
	}
}
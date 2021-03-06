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
	 * @param string $headline
	 * @param int $callable
	 * @param string $expiration
	 * @param string $email
     * @return void
     */
    public function newAction($headline = "", $callable = 0, $expiration = "", $email = "") {
        $publicKey = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.publicKey");
        $callableOptions = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.callableOptions");
		$this->view->assignMultiple(array(
			"headline" => $headline,
			"expiration" => $expiration,
			"callable" => $callable,
			"email" => $email,
			"callableOptions" => $callableOptions,
            "publicKey" => $publicKey,
		));
	}

	/**
	 * @return void
	 */
	public function listNotesAction() {
		if(!$this->authenticationManager->isAuthenticated()) {
			$this->addFlashMessage("Bitte einloggen um Notes anzusehen!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->redirect("new", "CreatePass");
		}
		/** @var User $user */
		$user = $this->accountService->getActiveAuthenticatedUser();
		$this->view->assignMultiple(array(
			"entries" => $user->getActiveEntries(),
			"expired" => $user->getExpiredEntries()
		));
	}

	/**
	* @return void
	* @param string $passId
	*/
    public function generateLinkAction($passId) {
		$link = $this->request->getHttpRequest()->getBaseUri()."id/".$passId;
		$this->view->assignMultiple(array(
			"link" => $link,
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

        $privateKey = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.privateKey");

        if($this->captchaService->verifyCaptcha($this->request->getArgument('g-recaptcha-response'), $privateKey)) {
            $callableOptions = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.callableOptions");

            if($expiration == "") {
                $expiration = new \DateTime('+1 hour');
            } else {
                $expiration = new \DateTime($expiration);
                if($expiration <= new \DateTime('now')) {
                    $this->addFlashMessage("Verfügbarkeits Datum ist überschritten!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
                    $this->redirect("new", "CreatePass", NULL, array(
                        "headline" => $newPass->getHeadline(),
                        "callable" => $newPass->getCallable(),
                        "sendEmail" => $newPass->getSendEmail(),
                        "email" => $newPass->getEmail()
                    ));
                }
            }

            $newPass->setUser($this->accountService->getActiveAuthenticatedUser());
            $newPass->setExpiration($expiration);

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
                    ->setSubject($name.' hat eine geheime Note mit Ihnen geteilt!')
                    ->setBody('Neue Note für Sie: '.$this->request->getHttpRequest()->getBaseUri()."id/".$newPass->getId().' /// Die Note kann '.$newPass->getCallable().' mal bis zum '.$newPass->getExpiration()->format("Y-m-d H:i")." entschlüsselt werden. /// Bitte folgendes Passwort nutzen: ".\Passbin\Base\Domain\Service\CryptionService::decryptData($newPass->getPassword()))
                    ->send();
            }
            $this->redirect("generateLink", "CreatePass", "Passbin.Base", array("passId" => $newPass->getId()));
        } else {
            $this->addFlashMessage("Captcha konnte nicht verifiziert werden.", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
            $this->redirect("new", "CreatePass", "Passbin.Base");
        }
	}
}
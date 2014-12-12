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

class CreatePassController extends \Passbin\Base\Controller\BaseController {

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
	 * passRepository
	 *
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
     * @return void
     */
    public function newAction() {
		/** @var User $user */
		$entrys = array();

		$account = $this->authenticationManager->getSecurityContext()->getAccount();
		$user = $this->userRepository->findOneByAccount($account);

		foreach($user->getPassEntrys() as $entry) {
			/** @var Pass $entry */
			if($entry->getExpiration()->format("Y-m-d H:i:s") < date("Y-m-d H:i:s"))
			{
				$this->passRepository->remove($entry);
				$this->persistenceManager->persistAll();
			} else {
				$entrys[] = $entry;
			}
		}

		$callableOptions = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, "Passbin.Pass.callableOptions");

		$this->view->assignMultiple(array(
			"entrys" => $entrys,
			"callableOptions" => $callableOptions
		));
    }

    /**
     * @return void
     * @param string $passId
     */
    public function generateLinkAction($passId) {
        $link = $this->request->getHttpRequest()->getBaseUri()."id/".$passId;
        $this->view->assign("link", $link);
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
        $newPass->setCreator($this->request->getHttpRequest()->getClientIpAddress());
        $newPass->setCreationDate(new \DateTime("now"));
        $this->passRepository->add($newPass);

        if ($newPass->getSendEmail() === "yes") {
            $mail = new \TYPO3\SwiftMailer\Message();
            $mail->setFrom(array('noreply@passb.in ' => 'Passbin'))
                ->setTo(array($newPass->getEmail() => ''))
                ->setSubject('Someone shared a secure Note with you!')
                ->setBody('New secure Note for you. Here: '.$this->request->getHttpRequest()->getBaseUri()."id/".$newPass->getId())
                ->send();
        }

        $this->redirect("generateLink", "CreatePass", "Passbin.Base", array("passId" => $newPass->getId()));
    }
}
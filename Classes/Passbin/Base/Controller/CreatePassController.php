<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class CreatePassController extends \Passbin\Base\Controller\BaseController {

    /**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('foos', array(
			'bar', 'baz'
		));
	}

    /**
     * @return void
     */
    public function newAction() {
        $callableOptions = array(1,2,3,4,5);
		$this->view->assign("callableOptions", $callableOptions);
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
	 * @param string $callable
	 * @param string $expiration
     */
    public function createAction(\Passbin\Base\Domain\Model\Pass $newPass, $callable, $expiration) {
		$callableOptions = array(1,2,3,4,5);

		if($expiration == "") {
			$expiration = date('Y-m-d H:i:s', strtotime('1 hour'));
		} else {
			$expiration = date('Y-m-d H:i:s', strtotime($expiration));

			if($expiration <= date('Y-m-d H:i:s')) {
				$this->addFlashMessage("Expiration Date is invalid", "Error!", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
				$this->redirect("new", "CreatePass");
			}
		}

		$newPass->setExpiration(new \DateTime($expiration));
		$newPass->setCallable($callableOptions[$callable]);
        $newPass->setId(uniqid());
        $newPass->setSecure($this->encryptData($newPass->getSecure()));
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
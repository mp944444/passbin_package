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
        $this->view->assign('foos', array(
            'bar', 'baz'
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
     */
    public function createAction(\Passbin\Base\Domain\Model\Pass $newPass) {

        $newPass->setId(uniqid());

        $newPass->setCreator($this->request->getHttpRequest()->getClientIpAddress());
        $newPass->setCreationDate(new \DateTime("now"));

        //var_dump($newPass);die();

        $this->passRepository->add($newPass);
        $this->redirect("generateLink", "CreatePass", "Passbin.Base", array("passId" => $newPass->getId()));
    }
}
<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class ViewPassController extends BaseController {

	/**
	 * @return void
     * @param string $id
	 */
	public function indexAction($id) {
		$pass = $this->passRepository->findById($id)->getFirst();
        if ($pass !== NULL) {
            $this->view->assign('passId', $pass->getId());
            $this->view->assign('found', true);
        } else {
            $this->view->assign('found', false);
        }
	}

    /**
     * @return void
     * @param string $passId
     * @param string $password
     */
    public function decryptAction($passId, $password) {
        /**
         * @var \Passbin\Base\Domain\Model\Pass $pass
         */
        $pass = $this->passRepository->findById($passId)->getFirst();

        if ($pass !== NULL) {
            if ($pass->getPassword() == $password) {
                $this->addFlashMessage("The note has been removed now. Please save it elsewhere.", "Notice!", \TYPO3\Flow\Error\Message::SEVERITY_NOTICE);
                $pass->setSecure($this->decryptData($pass->getSecure()));
                $this->view->assign('pass',$pass);
                $this->passRepository->remove($pass);
            } else {

                $this->addFlashMessage('Wrong Password', 'password', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
                $this->redirectToRequest($this->request->getReferringRequest());
            }
        } else {
            $this->addFlashMessage("The note is not there anymore.");
            $this->redirect("new", "CreatePass");
        }
    }
}
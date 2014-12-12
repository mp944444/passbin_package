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
		/**
		 * @var \Passbin\Base\Domain\Model\Pass $pass
		 */
		$pass = $this->passRepository->findById($id)->getFirst();
        if ($pass !== NULL) {
			$expiration = $pass->getExpiration();

			if(date('Y-m-d H:i:s') > $expiration->format("Y-m-d H:i:s")) {
				$this->passRepository->remove($pass);
				$this->persistenceManager->persistAll();
				$this->addFlashMessage("Note does not exist", "Error!", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
				$this->redirect("new", "CreatePass");
			}
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
				if($pass->getCallable() == 1) {
					$this->passRepository->remove($pass);
					$this->addFlashMessage("The note has been removed now. Please save it elsewhere.", "Notice!", \TYPO3\Flow\Error\Message::SEVERITY_NOTICE);
				} else {
					$pass->setCallable($pass->getCallable()-1);
					$this->passRepository->update($pass);
				}
				$encrypted = \Passbin\Base\Domain\Service\CryptionService::decryptData($pass->getSecure()); //->decryptData($pass->getSecure());
				$this->view->assign('encrypted', $encrypted);
                $this->view->assign('pass',$pass);
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
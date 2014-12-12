<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class ViewPassController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
				$pass->setSecure("");
				$pass->setPassword("");
				$this->passRepository->update($pass);
				$this->persistenceManager->persistAll();
				$this->addFlashMessage("Note does not exist", "Error!", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
				$this->redirect("new", "CreatePass");
			}
			$login = 0;
			if($this->authenticationManager->isAuthenticated()) {
				$login = 1;
			}
			$this->view->assignMultiple(array(
				"login" => $login,
				"passId" => $pass->getId(),
				"found" => true
			));
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
					$encrypted = \Passbin\Base\Domain\Service\CryptionService::decryptData($pass->getSecure()); //->decryptData($pass->getSecure());
					$pass->setPassword("");
					$pass->setSecure("");
					$pass->setCallable(0);
					$this->addFlashMessage("The note has been removed now. Please save it elsewhere.", "Notice!", \TYPO3\Flow\Error\Message::SEVERITY_NOTICE);
				} else {
					$encrypted = \Passbin\Base\Domain\Service\CryptionService::decryptData($pass->getSecure()); //->decryptData($pass->getSecure());
					$pass->setCallable($pass->getCallable()-1);
				}
				$this->passRepository->update($pass);
				$login = 0;
				if($this->authenticationManager->isAuthenticated()) {
					$login = 1;
				}
				$this->view->assignMultiple(array(
					"login" => $login,
					"encrypted" => $encrypted,
					"pass" => $pass
				));
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
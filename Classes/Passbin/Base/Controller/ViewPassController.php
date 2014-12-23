<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Passbin\Base\Domain\Model\User;
use Passbin\Base\Domain\Model\Pass;
use TYPO3\Flow\Error\Message;

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
	 * @var \Passbin\Base\Domain\Service\AccountService
	 * @FLow\Inject
	 */
	protected $accountService;

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
			if(!$pass->isValid()) {
				$this->addFlashMessage("Note does not exist or is no longer valid!", "", \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
				$this->redirect("new", "CreatePass");
			}
			$this->view->assignMultiple(array(
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
			if (\Passbin\Base\Domain\Service\CryptionService::decryptData($pass->getPassword()) == $password) {
				if($pass->getCallable() == 1) {
					$encrypted = \Passbin\Base\Domain\Service\CryptionService::decryptData($pass->getSecure());
					$pass->setPassword("");
					$pass->setSecure("");
					$pass->setCallable(0);
					$this->addFlashMessage("The note has been removed now. Please save it elsewhere!", "", \TYPO3\Flow\Error\Message::SEVERITY_NOTICE);
				} else {
					$encrypted = \Passbin\Base\Domain\Service\CryptionService::decryptData($pass->getSecure());
					$pass->setCallable($pass->getCallable()-1);
				}
				$this->passRepository->update($pass);
				$this->view->assignMultiple(array(
					"encrypted" => $encrypted,
					"pass" => $pass
				));
            } else {
                $this->addFlashMessage('Wrong Password!', '', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
                $this->redirectToRequest($this->request->getReferringRequest());
            }
        } else {
            $this->addFlashMessage("The note is not there anymore!");
            $this->redirect("new", "CreatePass");
        }
    }

	/**
	 * @param string $id
	 * @return void
	 */
	public function deleteNoteAction($id) {
		/**
		 * @var User $user
		 * @var Pass $pass
		 */
		if($this->authenticationManager->isAuthenticated()) {
			$user = $this->accountService->getActiveAuthenticatedUser();

			$pass = $this->passRepository->findOneById($id);

			if($pass === NULL) {
				$this->addFlashMessage("The Note does not exist!", "", Message::SEVERITY_ERROR);
				$this->redirect("listNotes", "CreatePass");
			}

			$noteOwner = $pass->getUser();

			if($user == $noteOwner) {
				$this->passRepository->remove($pass);
				$this->persistenceManager->persistAll();
			} else {
				$this->addFlashMessage("You can not delete a Note which is not yours!", "", Message::SEVERITY_ERROR);
				$this->redirect("listNotes", "CreatePass");
			}
			$this->addFlashMessage("Note has been deleted!", "", Message::SEVERITY_OK);
			$this->redirect("listNotes", "CreatePass");
		} else {
			$this->redirect("start", "User");
		}
	}
}
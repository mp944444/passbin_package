<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class CreatePassController extends \Passbin\Base\Controller\BaseController {

    /**
     * passRepository
     *
     * @var \Passbin\Base\Domain\Repository\PassRepository
     * @Flow\Inject
     */
    protected $passRepository;

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
     */
    public function createAction(\Passbin\Base\Domain\Model\Pass $newPass) {

        $newPass->setId(uniqid());

        $newPass->setCreator($this->request->getHttpRequest()->getClientIpAddress());
        $newPass->setCreationDate(new \DateTime("now"));

        //var_dump($newPass);die();

        $this->passRepository->add($newPass);
        $this->redirect("new");
    }
}
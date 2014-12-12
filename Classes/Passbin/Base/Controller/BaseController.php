<?php
namespace Passbin\Base\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class BaseController extends \TYPO3\Flow\Mvc\Controller\ActionController {

    /**
     * passRepository
     *
     * @var \Passbin\Base\Domain\Repository\PassRepository
     * @Flow\Inject
     */
    protected $passRepository;

	/**
	 * @FLow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;



	public function initializeAction() {
		if(!$this->authenticationManager->isAuthenticated()) {
			$this->addFlashMessage("Please log in first!", "Warning!", \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->redirect("start", "User");
		}
	}

    /**
	 *
	 * @todo auslagern in einen EncryptionService
	 * @todo functionen als static machen
     * getEncKey
     *
     * @return string
     */
    public function getEncKey() {
        if (!file_exists(FLOW_PATH_DATA . 'Persistent/EncryptionKey')) {
            file_put_contents(FLOW_PATH_DATA . 'Persistent/EncryptionKey', bin2hex(\TYPO3\Flow\Utility\Algorithms::generateRandomBytes(96)));
        }
        return file_get_contents(FLOW_PATH_DATA . 'Persistent/EncryptionKey');
    }

    /**
     * encryptData
     *
     * @param string $data
     * @throws \TYPO3\Flow\Exception
     * @return string
     */
    public function encryptData($data) {
        if(!function_exists('mcrypt_module_open')) {
            throw new \TYPO3\Flow\Exception("mcrypt not found. Please install php-mcrypt.");
        }

        $key = $this->getEncKey();
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $passcrypt = trim(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, mb_strcut($key,'0','32'), trim($data), MCRYPT_MODE_ECB, $iv));
        $encode = base64_encode($passcrypt);
        return $encode;
    }

    /**
     * decryptData
     *
     * @param string $data
     * @throws \TYPO3\Flow\Exception
     * @return string
     */
    public function decryptData($data) {
        if(!function_exists('mcrypt_module_open')) {
            throw new \TYPO3\Flow\Exception("mcrypt not found. Please install php-mcrypt.");
        }

        $key = $this->getEncKey();
        $decoded = base64_decode($data);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, mb_strcut($key,'0','32'), trim($decoded), MCRYPT_MODE_ECB, $iv));
        return $decrypted;
    }
}
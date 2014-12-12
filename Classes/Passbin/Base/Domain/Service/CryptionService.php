<?php
namespace Passbin\Base\Domain\Service;

use TYPO3\Flow\Annotations as Flow;
/**
 * Class CryptionService
 * @package Passbin\Base\Domain\Service
 */
class CryptionService {
	/**
	 *
	 * getEncKey
	 *
	 * @return string
	 */
	static function getEncKey() {
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
	static function encryptData($data) {
		if(!function_exists('mcrypt_module_open')) {
			throw new \TYPO3\Flow\Exception("mcrypt not found. Please install php-mcrypt.");
		}

		$key = CryptionService::getEncKey();
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
	static function decryptData($data) {
		if(!function_exists('mcrypt_module_open')) {
			throw new \TYPO3\Flow\Exception("mcrypt not found. Please install php-mcrypt.");
		}

		$key = CryptionService::getEncKey();
		$decoded = base64_decode($data);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, mb_strcut($key,'0','32'), trim($decoded), MCRYPT_MODE_ECB, $iv));
		return $decrypted;
	}
} 
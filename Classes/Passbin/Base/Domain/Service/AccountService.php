<?php
/**
 * Created by PhpStorm.
 * User: Marcel P
 * Date: 16.12.2014
 * Time: 12:20
 */

namespace Passbin\Base\Domain\Service;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Account;
/**
 * Class AccountService
 * @package Passbin\Base\Domain\Service
 */
class AccountService {
	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @param string $identifier
	 * @return \TYPO3\Flow\Security\Account
	 */
	public function getAccount($identifier) {
		return $this->getAccountByIdentifierOrAuthenticationProviderName($identifier, "DefaultProvider");
	}


	/**
	 * @param string $identifier
	 * @return \TYPO3\Flow\Security\Account
	 */
	protected function getAccountByIdentifierOrAuthenticationProviderName($identifier) {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($identifier, "DefaultProvider");

		if ($account === NULL) {
			return FALSE;
		}
		return $account;
	}

	/**
	 * @param Account $account
	 * @param $password
	 * @param string $passwordHashingStrategy
	 */
	public function resetPassword(Account $account, $password, $passwordHashingStrategy = 'default') {
		$account->setCredentialsSource($this->hashService->hashPassword($password, $passwordHashingStrategy));
		$this->accountRepository->update($account);
	}
}
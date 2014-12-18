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
	 * @var \Passbin\Base\Domain\Repository\UserRepository
	 * @Flow\Inject
	 */
	protected $userRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * @param string $identifier
	 * @return \TYPO3\Flow\Security\Account
	 */
	public function getAccount($identifier) {
		return $this->getAccountByIdentifierOrAuthenticationProviderName($identifier, "DefaultProvider");
	}

	/**
	 * @param string $identifier
	 * @param string $provider
	 * @return \TYPO3\Flow\Security\Account
	 */
	protected function getAccountByIdentifierOrAuthenticationProviderName($identifier, $provider = "DefaultProvider") {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($identifier, $provider);
		if ($account === NULL) {
			return FALSE;
		}
		return $account;
	}

	/**
	 * @todo umbenennen in setPassword
	 * @param Account $account
	 * @param $password
	 * @param string $passwordHashingStrategy
	 */
	public function resetPassword(Account $account, $password, $passwordHashingStrategy = 'default') {
		$account->setCredentialsSource($this->hashService->hashPassword($password, $passwordHashingStrategy));
		$this->accountRepository->update($account);
	}

	/**
	 * @param string $username
	 */
	public function getActiveUser($username) {
		$account = $this->getAccount($username);
		return $this->userRepository->findOneByAccount($account);
	}

	public function getActiveAuthenticatedUser() {
		$account = $this->authenticationManager->getSecurityContext()->getAccount();
		return $this->userRepository->findOneByAccount($account);
	}
}
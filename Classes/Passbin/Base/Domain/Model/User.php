<?php
namespace Passbin\Base\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Passbin.Base".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class User {
	/**
	 * @var \TYPO3\Flow\Security\Account
	 * @ORM\OneToOne
	 */
	protected $account;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection<Passbin\Base\Domain\Model\Pass>
	 * @ORM\OneToMany(mappedBy="user", cascade="persist")
	 */
	protected $passEntrys;

	/**
	 * @var string
	 */
	protected $firstname;

	/**
	 * @var string
	 */
	protected $lastname;

	/**
	 * @var \DateTime
	 */
	protected $lastLogin;

	/**
	 * @return \TYPO3\Flow\Security\Account
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 * @param \TYPO3\Flow\Security\Account $account
	 */
	public function setAccount($account)
	{
		$this->account = $account;
	}

	/**
	 * @return string
	 */
	public function getFirstname()
	{
		return $this->firstname;
	}

	/**
	 * @param string $firstname
	 */
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
	}

	/**
	 * @return string
	 */
	public function getLastname()
	{
		return $this->lastname;
	}

	/**
	 * @param string $lastname
	 */
	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getPassEntrys()
	{
		return $this->passEntrys;
	}

	/**
	 * @param \Doctrine\Common\Collections\ArrayCollection $passEntrys
	 */
	public function setPassEntrys($passEntrys)
	{
		$this->passEntrys = $passEntrys;
	}

	/**
	 * @param Pass $pass
	 */
	public function addPassEntry(Pass $pass) {
		if(!$this->passEntrys->contains($pass)) {
			$this->passEntrys->add($pass);
			$pass->setUser($this);
		}
	}

	/**
	 * @return \DateTime
	 */
	public function getLastLogin()
	{
		return $this->lastLogin;
	}

	/**
	 * @param \DateTime $lastLogin
	 */
	public function setLastLogin($lastLogin)
	{
		$this->lastLogin = $lastLogin;
	}
}
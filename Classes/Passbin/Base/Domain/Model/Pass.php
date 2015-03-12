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
class Pass {

    /**
     * @var \Passbin\Base\Domain\Repository\PassRepository
     * @Flow\Inject
     */
    protected $passRepository;

    /**
     * id
     * @var string
     */
    protected $id;

	/**
	 * @var \Passbin\Base\Domain\Model\User
	 * @ORM\ManyToOne(inversedBy="passEntrys")
	 */
	protected $user;

    /**
     * creator
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $creator = NULL;

    /**
     * headline
     *
     * @var string
     */
    protected $headline;

    /**
     * sendEmail
     *
     * @var string
     */
    protected $sendEmail;

    /**
     * email
     *
     * @var string
     * @Flow\Validate(type="EmailAddress")
     */
    protected $email = NULL;

    /**
     * password
     *
     * @var string
     */
    protected $password = NULL;

    /**
     * secure
     *
     * @var string
     * @ORM\Column(type="text")
     */
    protected $secure = NULL;

    /**
     * creationDate
     *
     * @var \DateTime
     */
    protected $creationDate;

	/**
	 * @var \DateTime
	 */
	protected $expiration;

	/**
	 * @var int
	 */
	protected $callable;

    /**
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }


    /**
     * @param string $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }


    /**
     * @return mixed
     */
    public function getHeadline()
    {
        return $this->headline;
    }

    /**
     * @param mixed $headline
     */
    public function setHeadline($headline)
    {
        $this->headline = $headline;
    }


    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @return string
     */
    public function getSendEmail()
    {
        return $this->sendEmail;
    }

    /**
     * @param string $sendEmail
     */
    public function setSendEmail($sendEmail)
    {
        $this->sendEmail = $sendEmail;
    }

    /**
     * @param string $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

	/**
	 * @return \DateTime
	 */
	public function getExpiration()
	{
		return $this->expiration;
	}

	/**
	 * @param \DateTime $expiration
	 */
	public function setExpiration($expiration)
	{
		$this->expiration = $expiration;
	}

	/**
	 * @return int
	 */
	public function getCallable()
	{
		return $this->callable;
	}

	/**
	 * @param int $callable
	 */
	public function setCallable($callable)
	{
		$this->callable = $callable;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

    /**
     * @return bool
     */
    public function isValid() {
        $valid = true;

        if($this->callable <= 0)
            $valid = false;

        if($this->getExpiration() == NULL)
            $valid = false;

        $now = new \DateTime('now');
        if($this->getExpiration() <= $now)
            $valid = false;

        // If is not valid reset password and secure
        if(!$valid) {
            $this->setPassword(NULL);
            $this->setSecure(NULL);
            $this->passRepository->update($this);
        }

        return $valid;
    }
}
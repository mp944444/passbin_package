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

}
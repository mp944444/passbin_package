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
     */
    protected $creator;

    /**
     * headline
     *
     * @var string
     */
    protected $headline;

    /**
     * text
     *
     * @var string
     * @ORM\Column(type="text")
     */
    protected $pass = '';

    /**
     * creationDate
     *
     * @var \DateTime
     */
    protected $creationDate;
}
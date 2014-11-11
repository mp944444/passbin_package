<?php
namespace Passbin\Base\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
/**
 * A validator for checking items against foos.
 */
class PassSendMailValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator
{

    /**
     * @var array
     */
    protected $supportedOptions = array(
        'pass' => array(NULL, 'pass', '\Passbin\Base\Domain\Model\Pass', true),
    );

    /**
     * Check if the given value is a valid foo item. What constitutes a valid foo
     * is determined through the 'foo' option.
     *
     * @param \Passbin\Base\Domain\Model\Pass $value
     * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
     * @return void
     */
    protected function isValid($value)
    {
        if (!isset($this->options['pass'])) {
            throw new \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException(
                'The option "pass" for this validator needs to be specified', 12346788
            );
        }

        if ($value->getSendEmail() == "yes") {
            $emailValidator = new \TYPO3\Flow\Validation\Validator\EmailAddressValidator();
            $emailvalid = $emailValidator->validate($value->getEmail());
            if ($emailvalid->hasErrors()) {
                $this->addError($emailvalid->getFirstError()->getMessage(),$emailvalid->getFirstError()->getCode(),$emailvalid->getFirstError()->getArguments());
            }
            $notEmptyValidator = new \TYPO3\Flow\Validation\Validator\NotEmptyValidator();
            $notemptyvalid = $notEmptyValidator->validate($value->getEmail());
            if ($notemptyvalid->hasErrors()) {
                $this->addError($notemptyvalid->getFirstError()->getMessage(),$notemptyvalid->getFirstError()->getCode(),$notemptyvalid->getFirstError()->getArguments());
            }
        }
    }
}
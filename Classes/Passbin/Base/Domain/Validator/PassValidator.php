<?php

/**
 * A validator for checking items against foos.
 */
class PassValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator
{

    /**
     * @var array
     */
    protected $supportedOptions = array(
        'checkbox' => array(NULL, 'The checkbox has to contain "yes"', 'string', true),
        'email' => array(NULL, 'Email to verify', 'string', true)
    );

    /**
     * Check if the given value is a valid foo item. What constitutes a valid foo
     * is determined through the 'foo' option.
     *
     * @param mixed $checkbox
     * @param $email
     * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
     * @internal param string $value
     * @return void
     */
    protected function isValid($checkbox, $email)
    {
        if (!isset($this->options['checkbox'])) {
            throw new \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException(
                'The option "checkbox" for this validator needs to be specified', 12346788
            );
        }
        if (!isset($this->options['email'])) {
            throw new \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException(
                'The option "email" for this validator needs to be specified', 12346788
            );
        }

        if ($checkbox !== $this->options['checkbox']) {
            $this->addError('The value must be equal to "%s"', 435346321, array($this->options['checkbox']));
        }
        if ($email !== $this->options['email']) {
            $this->addError('The value must be equal to "%s"', 435346321, array($this->options['email']));
        }

        if ($checkbox == "yes") {
            $emailValidator = new \TYPO3\Flow\Validation\Validator\EmailAddressValidator;
            $emailvalid = $emailValidator->validate($email);
            if ($emailvalid->hasErrors()) {
                $this->addError($emailvalid->getFirstError()->getMessage(),$emailvalid->getFirstError()->getCode(),$emailvalid->getFirstError()->getArguments());
            }
            
        }
    }
}
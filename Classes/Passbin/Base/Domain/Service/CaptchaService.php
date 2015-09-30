<?php
namespace Passbin\Base\Domain\Service;

use TYPO3\Flow\Annotations as Flow;
/**
 * Class CryptionService
 * @package Passbin\Base\Domain\Service
 */
class CaptchaService {
    /**
     * @param string $response
     * @return bool
     */
    public function verifyCaptcha($response, $privateKey) {
        $postdata = http_build_query(
            array(
                'secret'    => $privateKey,
                'response'  => $response
            )
        );

        $opts = array('http' =>
            array(
                'method'    => 'POST',
                'header'    => 'Content-type: application/x-www-form-urlencoded',
                'content'   => $postdata
            )
        );

        $context = stream_context_create($opts);
        $result = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context));

        $result = get_object_vars($result);

        if($result['success']) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
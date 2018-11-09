<?php
/**
 * Constant Contact plugin for Craft CMS 3.x
 *
 * Basic Contact Contact signup form
 *
 * @link      https://madebyraygun.com
 * @copyright Copyright (c) 2018 Dalton Rooney
 */

namespace madebyraygun\constantcontact\services;

use madebyraygun\constantcontact\ConstantContact as Plugin;
use Craft;
use craft\base\Component;
use Classy\ConstantContact\ConstantContactClient as Client;

/**
 * ConstantContactService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Dalton Rooney
 * @package   ConstantContact
 * @since     0.0.1
 */
class ConstantContactService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     *
     * @return mixed
     */
    public function subscribe($email) {
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();
        $client = new Client($settings->key, $settings->token);
        $payload = [
            'lists' => [
                [
                    'id' => $settings->list
                ]
            ],
            'email_addresses' => [
                [
                    'email_address' => $email
                ]
            ]
        ];

        try {
            $response = $client->addContact($payload);
            Craft::info('success',__METHOD__);
            return [
                'success' => true,
                'message' => "You've been added to the list."
            ];
        } catch (Exception $e) {
            $httpStatusCode = $e->getCode();
            Craft::info('fail: ' . $httpStatusCode,__METHOD__);
            return [
                'success' => false,
                'message' => "Something didn't work."
            ];
            
        }
    }

    /**
     * Validate an email address.
     * Provide email address (raw input)
     * Returns true if the email address has the email
     * address format and the domain exists.
     *
     * @param string $email Email to validate
     *
     * @return boolean
     * @author André Elvan
     */
    public function validateEmail($email)
    {
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex + 1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                // local part length exceeded
                $isValid = false;
            } else {
                if ($domainLen < 1 || $domainLen > 255) {
                    // domain part length exceeded
                    $isValid = false;
                } else {
                    if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                        // local part starts or ends with '.'
                        $isValid = false;
                    } else {
                        if (preg_match('/\\.\\./', $local)) {
                            // local part has two consecutive dots
                            $isValid = false;
                        } else {
                            if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                                // character not valid in domain part
                                $isValid = false;
                            } else {
                                if (preg_match('/\\.\\./', $domain)) {
                                    // domain part has two consecutive dots
                                    $isValid = false;
                                } else {
                                    if
                                    (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                                        str_replace("\\\\", "", $local))
                                    ) {
                                        // character not valid in local part unless
                                        // local part is quoted
                                        if (!preg_match('/^"(\\\\"|[^"])+"$/',
                                            str_replace("\\\\", "", $local))
                                        ) {
                                            $isValid = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                // domain not found in DNS
                $isValid = false;
            }
        }
        return $isValid;
    }
}

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
use Ctct\Components\Contacts\Contact;
use Ctct\ConstantContact;
use Ctct\Exceptions\CtctException;
use Craft;
use craft\base\Component;

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
        $cc = new ConstantContact($settings->key);
        $response = $cc->contactService->getContacts($settings->token, array("email" => $email));
        $returnCode = array();
        if (empty($response->results)) {
            $action = "Creating Contact";
            $contact = new Contact();
            $contact->addEmail($email);
            $contact->addList($settings->list);
            $createContact = $cc->contactService->addContact($settings->token, $contact, false);
            if ( $createContact->status == 'ACTIVE') {
                $returnCode = array('success'=>true,'message'=>'You\'ve been added to the list.');
            } else {
                $returnCode = array('success'=>false,'message'=>'There was a problem adding you to the list. Please contact the administrator.');
            }
        } elseif ( $response->results[0]->status == 'ACTIVE' ) {
            $returnCode = array('success'=>false,'message'=>'It looks like you\'re already on this list! Want to subscribe a different email address?');
        } else {
            $returnCode = array('success'=>false,'message'=>'There was a problem adding you to the list. Please contact the administrator.');
        }
        return $returnCode;
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

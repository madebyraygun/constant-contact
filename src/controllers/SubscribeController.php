<?php
/**
 * Constant Contact plugin for Craft CMS 3.x
 *
 * Basic Contact Contact signup form
 *
 * @link      https://madebyraygun.com
 * @copyright Copyright (c) 2018 Dalton Rooney
 */

namespace madebyraygun\constantcontact\controllers;
use Craft;
use craft\web\Controller;
use madebyraygun\constantcontact\ConstantContact as Plugin;

/**
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Dalton Rooney
 * @package   ConstantContact
 * @since     0.0.1
 */
class SubscribeController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

  
    /**
     * Handle a request going to our plugin's actionSubscribe URL,
     * e.g.: actions/constant-contact/subscribe
     *
     * @return mixed
     */
    public function actionIndex()
    {
        // $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $email = $request->getParam('email');
        $plugin = Plugin::getInstance();
        $token = $plugin->getSettings()->token;

        if ($email === '' || !$this->validateEmail($email)) { // error, invalid email
            $result = array('success'=>false,'message'=>'Email address is invalid. Please try again.');
        } else {
            $result = $plugin->constantContactService->subscribe($email);
        }

        $redirect = $request->getParam('redirect', '');
   
        if ($request->getAcceptsJson()) {
            return $this->asJson($result);
        }

        if ($redirect !== '' && $result['success'] == true) {
            return $this->redirectToPostedUrl();
        }

        if ( !$result) {
            Craft::$app->getSession()->setError('Could not communicate with Constant Contact. Please try again later.');
        }

        if ( $result['success'] == false) {
            Craft::$app->getSession()->setError($result['message']);
        }

        if (  $result['success'] == true) {
            Craft::$app->getSession()->setNotice($result['message']);
        }
        
        return null;
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

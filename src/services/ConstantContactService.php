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
use Craft;
use craft\base\Component;
use madebyraygun\constantcontact\ConstantContact as Plugin;
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
    public function subscribe($email, $listID, $firstName = '', $lastName = '') {
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();
        $client = new Client($settings->key, $settings->token);
        $payload = [
            'lists' => [
                ['id' => $listID]
            ],
            'email_addresses' => [
                ['email_address' => $email]
            ],
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];

        try {
            $response = $client->addContact($payload);
            return [
                'success' => true,
                'message' => "You've been added to the list."
            ];
        } catch (\Exception $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);
            if ( !empty($error) ) {
                return [
                    'success' => false,
                    'message' => 'Error: ' . $error[0]['error_message']
                ];
            }
            return [
                'success' => false,
                'message' => 'An unknown error occurred.'
            ];
        }
    }
}

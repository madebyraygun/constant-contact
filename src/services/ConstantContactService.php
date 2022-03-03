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
use madebyraygun\constantcontact\lib\ConstantContactClient as Client;

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
        
        $options = [
            'query' => [
                'email' => $email
            ]
        ];

        try {
            $response = $client->request('GET', 'contacts', $options);
         } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An unknown error occurred.'
            ];
        }

        $responseObj = json_decode($response->getBody()->getContents());
        if ( !empty($responseObj->results) ) {
            return $this->updateContact($responseObj->results, $listID);
        } else {
            return $this->addContact($email, $listID);
        }

        return null;
    }

    /**
     *
     * @return mixed
     */
    private function addContact($email, $listID) {
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
            $response = $client->addContact($payload, 'ACTION_BY_VISITOR');
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
        return null;
    }

    /**
     *
     * @return mixed
     */
    private function updateContact($contact, $listID) {
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();
        $client = new Client($settings->key, $settings->token);

        $contact = reset($contact);
        $lists = $contact->lists;

        foreach ( $lists as $list ) {
            if ( $list->id == $listID ) {
                return [
                    'success' => false,
                    'message' => 'You\'re already subscribed to this list.'
                ];
            }
        }

        $contact->lists[] = (object) array('id'=>$listID,'status'=>'ACTIVE');

        try {
            $response = $client->updateContact($contact, 'ACTION_BY_VISITOR');
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
        return null;
    }
}

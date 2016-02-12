<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public $components = [
        'RequestHandler'
    ];

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */
    public function initialize()
    {
        //parent::initialize();
        $this->loadComponent(
            'Auth',
            [
                'authenticate' => [
                    'Form',
                    'ADmad/JwtAuth.Jwt' => [
                        'parameter' => '_token',
                        'userModel' => 'Users',
                        'scope' => ['Users.active' => 1],
                        'fields' => [
                            'id' => 'id'
                        ]
                    ]
                ]
            ]
        );
        $request = $this->request;
        $response = $this->response->cors($request, '*');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        //$this->Auth->allow(['view', 'index', 'checkExistence', 'edit',
        //'delete', 'add', 'twit', 'twit1', 'mention','token', 'getMention', 'mentionToDB']);
        $this->Auth->allow(['token', 'getMention']);
    }

    public $settingsTwitter = [
        'oauth_access_token' => '3555146480-EVgv9OGkcVIgxUaIoM2shbgkKAQZaJNogBE5ovF',
        'oauth_access_token_secret' => 'W78mlkW4mnNl92H7RO6eoDWtYwKU22F0sVxIyarvDqvxC',
        'consumer_key' => 'exnjHqKAXhefmLcUL93M0wBqa',
        'consumer_secret' => 'RLfNT2AeaTZZKs7RAGJCreK12rvSh98jTpGeM2l5maPYHgMQiF'
    ];

    public $baseTwitterUrl = 'https://api.twitter.com/1.1/';
}

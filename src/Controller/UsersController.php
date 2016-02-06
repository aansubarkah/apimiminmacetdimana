<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Network\Exception\UnauthorizedException;
use Cake\Utility\Security;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['add', 'token']);
    }

    public $limit = 25;

    public $paginate = [
        'fields' => ['Users.id', 'Users.username', 'Users.active'],
        'limit' => 25,
        'page' => 0,
        'order' => [
            'Users.name' => 'asc'
        ]
    ];

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Paginator');
    }

    /**
     * Index method
     *
     * parameters:
     *
     * $limit
     * $searchName for individual search
     * $page
     * $query
     *
     * @return void
     */
    public function index()
    {
        $limit = $this->limit;
        if (isset($this->request->query['limit'])) {
            if (is_numeric($this->request->query['limit'])) {
                $limit = $this->request->query['limit'];
            }
        }

        if (isset($this->request->query['searchName'])) {
            $searchName = trim($this->request->query['searchName']);
            $this->checkExistence($searchName, $limit);
        } else {
            $page = 1;
            $offset = 0;
            if (isset($this->request->query['page'])) {
                if (is_numeric($this->request->query['page'])) {
                    $page = (int)$this->request->query['page'];
                    $offset = ($page - 1) * $limit;
                }
            }

            $query = '';
            if (isset($this->request->query['query'])) {
                if (!empty(trim($this->request->query['query']))) {
                    $query = trim($this->request->query['query']);
                }
            }

            $conditions = ['Users.active' => true];

            if (!empty(trim($query))) {
                $conditions['LOWER(Users.username) LIKE'] = '%' . strtolower($query) . '%';
            }

            $users = $this->Users->find()
                ->where($conditions)
                ->order(['Users.username' => 'ASC'])
                ->limit($limit)->page($page)->offset($offset)
                ->toArray();

            $allUsers = $this->Users->find()->where($conditions);
            $total = $allUsers->count();

            // add group, to please Ember.js belongsTo
            // always use count($array), do not using $total
            // @param $countUsers
            $countUsers = count($users);
            for ($i = 0; $i < $countUsers; $i++) {
                $users[$i]['group'] = $users[$i]['group_id'];
            }

            $meta = [
                'total' => $total
            ];
            $this->set([
                'users' => $users,
                'meta' => $meta,
                '_serialize' => ['users', 'meta']
            ]);
        }
    }

    public function checkExistence($name = null, $limit = 25)
    {
        $data = [
            [
                'id' => 0,
                'username' => '',
                'active' => 0
            ]
        ];

        $fetchDataOptions = [
            'order' => ['Users.username' => 'ASC'],
            'limit' => $limit
        ];

        $query = trim(strtolower($name));

        if (!empty($query)) {
            $fetchDataOptions['conditions']['LOWER(Users.username) LIKE'] = '%' . $query . '%';
        }

        $user = $this->Users->find('all', $fetchDataOptions);

        if ($user->count() > 0) {
            $data = $user;
        }

        $this->set([
            'user' => $data,
            '_serialize' => ['user']
        ]);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id);
        $this->set([
            'user' => $user,
            '_serialize' => ['user']
        ]);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        //$this->request->data['active'] = 1;
        if (isset($this->request->data['user']['active'])) unset($this->request->data['user']['active']);
        if (isset($this->request->data['user']['id'])) unset($this->request->data['user']['id']);
        $this->request->data['user']['active'] = 1;

        $user = $this->Users->newEntity($this->request->data['user']);
        if ($this->Users->save($user)) {
            $data = [
                'id' => $user->id,
                'user' => $user->username,
                'email' => $user->email,
                'token' => $token = \JWT::encode(
                    [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'exp' => time() + 604800
                    ],
                    Security::salt())
                ];
        } else {
            $data = 'error!';
        }

        $this->set([
            'user' => $data,
            '_serialize' => ['user']
        ]);
        /*if (isset($this->request->data['user']['active'])) unset($this->request->data['user']['active']);

        $user = $this->Users->newEntity($this->request->data['user']);
        if ($this->request->is('post')) {
            if ($this->Users->save($user)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'user' => $message,
            '_serialize' => ['user']
        ]);*/
    }

    public function token()
    {
        $user = $this->request->data;
        $query = $this->Users->find('all');
        $query->select(['group_id']);
        $query->where(['username' => $user['username']]);
        $row = $query->first();

        if($row->group_id == 1){
            $user = $this->Auth->identify();
            if (!$user) {
                throw new UnauthorizedException('Invalid username or password');
            }

            $this->set([
                'token' => $token = \JWT::encode([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'exp' => time() + 604800
                ],
                Security::salt()
            ),
            //'username' => $user['username'],
            'email' => $user['email'],
            //'_serialize' => ['token','username']
            '_serialize' => ['token', 'email']
            //'_serialize' => ['token']
        ]);
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if (isset($this->request->data['user']['active'])) unset($this->request->data['user']['active']);

            $user = $this->Users->patchEntity($user, $this->request->data['user']);
            if ($this->Users->save($user)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'user' => $user,
            '_serialize' => ['user']
        ]);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $user = $this->Users->get($id);
        if ($this->request->is(['delete'])) {
            $user->active = false;
            if ($this->Users->save($user)) {
                $message = 'Deleted';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'user' => $user,
            '_serialize' => ['user']
        ]);
    }
}

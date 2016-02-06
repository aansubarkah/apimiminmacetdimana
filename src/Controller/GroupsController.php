<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Groups Controller
 *
 * @property \App\Model\Table\GroupsTable $Groups
 */
class GroupsController extends AppController
{
    /*public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        //$this->Auth->allow(['add', 'token']);
        //$this->Auth->allow(['token']);
    }*/

    public $limit = 25;

    public $paginate = [
        'fields' => ['Groups.id', 'Groups.groupname', 'Groups.active'],
        'limit' => 25,
        'page' => 0,
        'order' => [
            'Groups.name' => 'asc'
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

            $conditions = ['Groups.active' => true];

            if (!empty(trim($query))) {
                $conditions['LOWER(Groups.name) LIKE'] = '%' . strtolower($query) . '%';
            }

            $groups = $this->Groups->find()
                ->where($conditions)
                ->order(['Groups.name' => 'ASC'])
                ->limit($limit)->page($page)->offset($offset)
                ->toArray();

            $allGroups = $this->Groups->find()->where($conditions);
            $total = $allGroups->count();

            $meta = [
                'total' => $total
            ];
            $this->set([
                'groups' => $groups,
                'meta' => $meta,
                '_serialize' => ['groups', 'meta']
            ]);
        }
    }

    public function checkExistence($name = null, $limit = 25)
    {
        $data = [
            [
                'id' => 0,
                'name' => '',
                'active' => 0
            ]
        ];

        $fetchDataOptions = [
            'order' => ['Groups.name' => 'ASC'],
            'limit' => $limit
        ];

        $query = trim(strtolower($name));

        if (!empty($query)) {
            $fetchDataOptions['conditions']['LOWER(Groups.name) LIKE'] = '%' . $query . '%';
        }

        $group = $this->Groups->find('all', $fetchDataOptions);

        if ($group->count() > 0) {
            $data = $group;
        }

        $this->set([
            'group' => $data,
            '_serialize' => ['group']
        ]);
    }

    /**
     * View method
     *
     * @param string|null $id Weather id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $group = $this->Groups->get($id);
        $this->set([
            'group' => $group,
            '_serialize' => ['group']
        ]);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if(isset($this->request->data['group']['active'])) unset($this->request->data['group']['active']);

        $group = $this->Groups->newEntity($this->request->data['group']);
        if ($this->request->is('post')) {
            if ($this->Groups->save($group)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'group' => $message,
            '_serialize' => ['group']
        ]);
    }

    /**
     * Edit method
     *
     * @param string|null $id Weather id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $group = $this->Groups->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if(isset($this->request->data['group']['active'])) unset($this->request->data['group']['active']);

            $group = $this->Groups->patchEntity($group, $this->request->data['group']);
            if ($this->Groups->save($group)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'group' => $message,
            '_serialize' => ['group']
        ]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Weather id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $group = $this->Groups->get($id);
        if ($this->request->is(['delete'])) {
            $group->active = false;
            if ($this->Groups->save($group)) {
                $message = 'Deleted';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'group' => $message,
            '_serialize' => ['group']
        ]);
    }
}

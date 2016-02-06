<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Respondents Controller
 *
 * @property \App\Model\Table\RespondentsTable $Respondents
 */
class RespondentsController extends AppController
{
    public $limit = 25;

    public $paginate = [
        'fields' => ['Respondents.id', 'Respondents.name', 'Respondents.active'],
        'limit' => 25,
        'page' => 0,
        'order' => [
            'Respondents.name' => 'asc'
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

            $conditions = ['Respondents.active' => true];

            if (!empty(trim($query))) {
                $conditions['LOWER(Respondents.name) LIKE'] = '%' . strtolower($query) . '%';
            }

            $respondents = $this->Respondents->find()
                ->where($conditions)
                ->order(['Respondents.name' => 'ASC'])
                ->limit($limit)->page($page)->offset($offset)
                ->toArray();

            $allRespondents = $this->Respondents->find()->where($conditions);
            $total = $allRespondents->count();

            // add group, to please Ember.js belongsTo
            // always use count($array), do not using $total
            // @param $countRespondents
            $countRespondents = count($respondents);
            for ($i = 0; $i < $countRespondents; $i++) {
                $respondents[$i]['group'] = $respondents[$i]['group_id'];
            }

            $meta = [
                'total' => $total
            ];
            $this->set([
                'respondents' => $respondents,
                'meta' => $meta,
                '_serialize' => ['respondents', 'meta']
            ]);
        }
    }

    /**
     * View method
     *
     * @param string|null $id Respondent id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $respondent = $this->Respondents->get($id);
        $this->set([
            'respondent' => $respondent,
            '_serialize' => ['respondent']
        ]);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if ($this->request->is('post')) {
            if (isset($this->request->data['respondent']['active'])) unset($this->request->data['respondent']['active']);
            if (isset($this->request->data['respondent']['id'])) unset($this->request->data['respondent']['id']);
            $this->request->data['respondent']['active'] = true;

            $respondent = $this->Respondents->newEntity($this->request->data['respondent']);
            $this->Respondents->save($respondent);

            $this->set([
                'respondent' => $respondent,
                '_serialize' => ['respondent']
            ]);
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Respondent id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $respondent = $this->Respondents->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if (isset($this->request->data['respondent']['active'])) unset($this->request->data['respondent']['active']);

            $respondent = $this->Respondents->patchEntity($respondent, $this->request->data['respondent']);
            if ($this->Respondents->save($respondent)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'respondent' => $message,
            '_serialize' => ['respondent']
        ]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Respondent id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $respondent = $this->Respondents->get($id);
        if ($this->request->is(['delete'])) {
            $respondent->active = false;
            if ($this->Respondents->save($respondent)) {
                $message = 'Deleted';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'respondent' => $respondent,
            '_serialize' => ['respondent']
        ]);
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
            'order' => ['Respondents.name' => 'ASC'],
            'limit' => $limit
        ];

        $query = trim(strtolower($name));

        if (!empty($query)) {
            $fetchDataOptions['conditions']['LOWER(Respondents.name) LIKE'] = '%' . $query . '%';
        }
        $fetchDataOptions['conditions']['active'] = true;

        $respondent = $this->Respondents->find('all', $fetchDataOptions);

        if ($respondent->count() > 0) {
            $data = $respondent;
        }

        $this->set([
            'respondent' => $data,
            '_serialize' => ['respondent']
        ]);
    }
}

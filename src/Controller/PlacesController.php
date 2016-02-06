<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Places Controller
 *
 * @property \App\Model\Table\PlacesTable $Places
 */
class PlacesController extends AppController
{
    public $limit = 25;

    public $paginate = [
        'fields' => ['Places.id', 'Places.name', 'Places.active'],
        'limit' => 25,
        'page' => 0,
        'order' => [
            'Places.name' => 'asc'
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
            $offset = 0;
            if (isset($this->request->query['page'])) {
                if (is_numeric($this->request->query['page'])) {
                    $offset = $this->request->query['page'] - 1;
                }
            }

            $query = '';
            if (isset($this->request->query['query'])) {
                if (!empty(trim($this->request->query['query']))) {
                    $query = trim($this->request->query['query']);
                }
            }

            $fetchDataOptions = [
                'conditions' => ['Places.active' => true],
                'order' => ['Places.name' => 'ASC'],
                'limit' => $limit,
                'page' => $offset
            ];

            if (!empty(trim($query))) {
                $fetchDataOptions['conditions']['LOWER(Places.name) LIKE'] = '%' . strtolower($query) . '%';
            }

            $this->paginate = $fetchDataOptions;
            $places = $this->paginate('Places');

            $allPlaces = $this->Places->find('all', $fetchDataOptions);
            $total = $allPlaces->count();

            $meta = [
                'total' => $total
            ];
            $this->set([
                'places' => $places,
                'meta' => $meta,
                '_serialize' => ['places', 'meta']
            ]);
        }
    }

    /**
     * View method
     *
     * @param string|null $id Place id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $place = $this->Places->get($id);
        $this->set([
            'place' => $place,
            '_serialize' => ['place']
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
            if (isset($this->request->data['place']['active'])) unset($this->request->data['place']['active']);
            if (isset($this->request->data['place']['id'])) unset($this->request->data['place']['id']);
            $this->request->data['place']['active'] = true;

            $place = $this->Places->newEntity($this->request->data['place']);
            $this->Places->save($place);

            $this->set([
                'place' => $place,
                '_serialize' => ['place']
            ]);
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Place id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $place = $this->Places->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if (isset($this->request->data['place']['active'])) unset($this->request->data['place']['active']);

            $place = $this->Places->patchEntity($place, $this->request->data['place']);
            if ($this->Places->save($place)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'place' => $message,
            '_serialize' => ['place']
        ]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Place id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $place = $this->Places->get($id);
        if ($this->request->is(['delete'])) {
            $place->active = false;
            if ($this->Places->save($place)) {
                $message = 'Deleted';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'place' => $place,
            '_serialize' => ['place']
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
            'order' => ['Places.name' => 'ASC'],
            'limit' => $limit
        ];

        $query = trim(strtolower($name));

        if (!empty($query)) {
            $fetchDataOptions['conditions']['LOWER(Places.name) LIKE'] = '%' . $query . '%';
        }

        $place = $this->Places->find('all', $fetchDataOptions);

        if ($place->count() > 0) {
            $data = $place;
        }

        $this->set([
            'place' => $data,
            '_serialize' => ['place']
        ]);
    }
}

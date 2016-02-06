<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Categories Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 */
class CategoriesController extends AppController
{
    public $limit = 25;

    public $paginate = [
        'fields' => ['Categories.id', 'Categories.name', 'Categories.active'],
        'limit' => 25,
        'page' => 0,
        'order' => [
            'Categories.name' => 'asc'
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
                'conditions' => ['Categories.active' => true],
                'order' => ['Categories.name' => 'ASC'],
                'limit' => $limit,
                'page' => $offset
            ];

            if (!empty(trim($query))) {
                $fetchDataOptions['conditions']['LOWER(Categories.name) LIKE'] = '%' . strtolower($query) . '%';
            }

            $this->paginate = $fetchDataOptions;
            $categories = $this->paginate('Categories');

            $allCategories = $this->Categories->find('all', $fetchDataOptions);
            $total = $allCategories->count();

            $meta = [
                'total' => $total
            ];
            $this->set([
                'categories' => $categories,
                'meta' => $meta,
                '_serialize' => ['categories', 'meta']
            ]);
        }
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
        $category = $this->Categories->get($id);
        $this->set([
            'category' => $category,
            '_serialize' => ['category']
        ]);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if(isset($this->request->data['category']['active'])) unset($this->request->data['category']['active']);

        $category = $this->Categories->newEntity($this->request->data['category']);
        if ($this->request->is('post')) {
            if ($this->Categories->save($category)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'category' => $message,
            '_serialize' => ['category']
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
        $category = $this->Categories->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if(isset($this->request->data['category']['active'])) unset($this->request->data['category']['active']);

            $category = $this->Categories->patchEntity($category, $this->request->data['category']);
            if ($this->Categories->save($category)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'category' => $message,
            '_serialize' => ['category']
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
        $category = $this->Categories->get($id);
        if ($this->request->is(['delete'])) {
            $category->active = false;
            if ($this->Categories->save($category)) {
                $message = 'Deleted';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'category' => $message,
            '_serialize' => ['category']
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
            'order' => ['Categories.name' => 'ASC'],
            'limit' => $limit
        ];

        $query = trim(strtolower($name));

        if (!empty($query)) {
            $fetchDataOptions['conditions']['LOWER(Categories.name) LIKE'] = '%' . $query . '%';
        }
        $fetchDataOptions['conditions']['active'] = true;

        $category = $this->Categories->find('all', $fetchDataOptions);

        if ($category->count() > 0) {
            $data = $category;
        }

        $this->set([
            'category' => $data,
            '_serialize' => ['category']
        ]);
    }
}

<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use TwitterAPIExchange;
use Cake\I18n\I18n;// Cake need this to save datetime field
use Cake\I18n\Time;// Cake need this to saving datetime field
use Cake\Database\Type;// Cake need this to saving datetime field

/**
 * Sources Controller
 *
 * @property \App\Model\Table\SourcesTable $Sources
 */
/**
 * Sources Controller
 *
 * @property \App\Model\Table\SourcesTable $Sources
 */
class SourcesController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Respondents']
        ];
        $this->set('sources', $this->paginate($this->Sources));
        $this->set('_serialize', ['sources']);
    }

    /**
     * View method
     *
     * @param string|null $id Source id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $source = $this->Sources->get($id, [
            'contain' => ['Respondents']
        ]);
        $this->set('source', $source);
        $this->set('_serialize', ['source']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $source = $this->Sources->newEntity();
        if ($this->request->is('post')) {
            $source = $this->Sources->patchEntity($source, $this->request->data);
            if ($this->Sources->save($source)) {
                $this->Flash->success(__('The source has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The source could not be saved. Please, try again.'));
            }
        }
        $respondents = $this->Sources->Respondents->find('list', ['limit' => 200]);
        $this->set(compact('source', 'respondents'));
        $this->set('_serialize', ['source']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Source id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $source = $this->Sources->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $source = $this->Sources->patchEntity($source, $this->request->data);
            if ($this->Sources->save($source)) {
                $this->Flash->success(__('The source has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The source could not be saved. Please, try again.'));
            }
        }
        $respondents = $this->Sources->Respondents->find('list', ['limit' => 200]);
        $this->set(compact('source', 'respondents'));
        $this->set('_serialize', ['source']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Source id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $source = $this->Sources->get($id);
        if ($this->Sources->delete($source)) {
            $this->Flash->success(__('The source has been deleted.'));
        } else {
            $this->Flash->error(__('The source could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Get Timeline Method
     *
     */
    private function getTimeline($latestTwitID = 0, $howManyTweets = 200) {
        $Twitter = new TwitterAPIExchange($this->settingsTwitter);

        $url = $this->baseTwitterUrl . 'statuses/home_timeline.json';
        $getfield = '?count=' . $howManyTweets;
        if ($latestTwitID > 0) {
            $getfield = $getfield . '&since_id=' . $latestTwitID;
        }
        $requestMethod = 'GET';

        $data = $Twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();

        return json_decode($data, true);
    }

    public function timelineToDB() {
        // first get the latest twitID from DB
        $getLatestTwitID = $this->Sources->find()
            ->select(['twitID'])
            ->where(['active' => true])
            ->order(['twitID' => 'DESC'])
            ->first();

        if ($getLatestTwitID['twitID'] > 0) {
            $latestTwitID = $getLatestTwitID['twitID'];
        } else {
            $latestTwitID = 1;
        }
        $dataStream = $this->getTimeline($latestTwitID, 200);
        $countDataStream = count($dataStream);
        //
        $dataToDisplay = [];
        $errorsOccured = [];
        if ($countDataStream > 0) {
            foreach ($dataStream as $datum) {
                $respondent = $this->Sources->Respondents->find()
                    ->select(['id'])
                    ->where(['isOfficial' => 1, 'contact' => '@' . $datum['user']['screen_name']])
                    ->first();
                $respondent_id = $respondent['id'];

                if (!empty($respondent_id)) {
                    $info = $datum['text'];
                    $created_at = date("Y-m-d H:i:s", strtotime($datum['created_at']));
                    Type::build('datetime')->useLocaleParser();//cakephp need this to save datetime field
                    $dataToSave = [
                        'respondent_id' => $respondent_id,
                        'lat' => 0,
                        'lng' => 0,
                        'twitID' => $datum['id'],
                        'twitTime' => new Time($created_at),
                        'info' => $datum['text'],
                        'url' => null,
                        'media' => null,
                        'isImported' => 0,
                        'active' => 1
                    ];
                    // if image do exists
                    if (array_key_exists('extended_entities', $datum) &&
                        array_key_exists('media', $datum['extended_entities']) &&
                        $datum['extended_entities']['media'][0]['type'] == 'photo'
                    ) {
                        $dataToSave['media'] = $datum['extended_entities']['media'][0]['media_url_https'];
                    }
                    // if url do exists
                    $twitURL = $this->findURLonText($info);
                    if ($twitURL !== null) {
                        $dataToSave['url'] = $twitURL;
                        $info = str_ireplace($twitURL, "", $info);
                        $info = trim($info);
                    }
                    $dataToSave['info'] = $info;

                    // if get precise location
                    if ($datum['geo'] !== null) {
                        $dataToSave['lat'] = $datum['geo']['coordinates'][0];
                        $dataToSave['lng'] = $datum['geo']['coordinates'][1];
                    }

                    // save twit to db
                    $source = $this->Sources->newEntity($dataToSave);
                    if ($this->Sources->save($source)) {
                        $dataToDisplay[]= $dataToSave;
                    } else {
                        //$errorsOccured[] = $this->Sources->validationErrors;
                    }
                }
            }
        }
        $this->set([
            'sources' => $dataToDisplay,
            'errors' => $errorsOccured,
            '_serialize' => ['sources', 'errors']
        ]);
    }

    private function findURLonText($text)
    {
        $regex = '$\b(https?|ftp|file)://[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]$i';
        $return = null;

        preg_match_all($regex, $text, $result, PREG_PATTERN_ORDER);
        $return = $result[0];
        return $return;
    }
}

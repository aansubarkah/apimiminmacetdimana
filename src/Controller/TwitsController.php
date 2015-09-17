<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use TwitterAPIExchange;

/**
 * Twits Controller
 * @property \App\Model\Table\MarkersTable $Markers
 * @property \App\Model\Table\RespondentsTable $Respondents
 */
class TwitsController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['mentionToDB']);
    }

    public $settingsTwitter = [
        'oauth_access_token' => '3555146480-sXfyGZDtrIDdzOMd1tt8srNWUijs7nCFfeag349',
        'oauth_access_token_secret' => 'fKQN5cTbpDEvic613JtfHoVz7LC9dlSfUsP0yohuwboxY',
        'consumer_key' => 'Bu8ZMGWX8LxqR0jjbCuKTvjfG',
        'consumer_secret' => 'Fx43AKjpEksdAcG7y7SmDVH4Y2UfOVgQTzwmzSRInuPZaokGrX'
    ];

    private $baseTwitterUrl = 'https://api.twitter.com/1.1/';

    //private $Markers = TableRegistry::get('Markers');
    private $Markers = null;
    private $Respondents = null;

    public function mention($since_id = null, $count = 800)
    {
        $Twitter = new TwitterAPIExchange($this->settingsTwitter);

        $url = $this->baseTwitterUrl . 'statuses/mentions_timeline.json';
        $getfield = '?count=' . $count;
        if ($since_id !== null) {
            $getfield = $getfield . '&since_id=' . $since_id;
        }
        $requestMethod = 'GET';

        $data = $Twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();

        $data = json_decode($data);
        $meta = [
            'total' => count($data)
        ];

        $this->set([
            'mentions' => $data,
            'meta' => $meta,
            '_serialize' => ['mentions', 'meta']
        ]);
    }

    private function getMention($since_id = 0, $count = 800)
    {
        $Twitter = new TwitterAPIExchange($this->settingsTwitter);

        $url = $this->baseTwitterUrl . 'statuses/mentions_timeline.json';
        $getfield = '?count=' . $count;
        if ($since_id > 0) {
            $getfield = $getfield . '&since_id=' . $since_id;
        }
        $requestMethod = 'GET';

        $data = $Twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();

        return json_decode($data, true);
    }

    public function mentionToDB()
    {
        $this->Markers = TableRegistry::get('Markers');

        // first get the latest twitID from DB
        $getLatestTwitID = $this->Markers->find()
            ->select(['twitID'])
            ->where(['active' => true, 'twitID IS NOT' => null])
            ->order(['twitID' => 'DESC'])
            ->first();

        if ($getLatestTwitID['twitID'] > 0) {
            $latestTwitID = $getLatestTwitID['twitID'];
        } else {
            //$latestTwitID = 642008117992001536;//first test twit mentioning @macetsurabaya
            $latestTwitID = 1;
        }

        // second grab twit
        $dataStream = $this->getMention($latestTwitID, 800);
        $countDataStream = count($dataStream);

        $dataToDisplay = [];
        // @todo better to return no data message after
        if ($countDataStream > 0) {
            foreach ($dataStream as $data) {
                if ($data['place'] !== null) {
                    $isTwitExists = $this->Markers->exists(['twitID' => $data['id'], 'active' => 1]);
                    if (!$isTwitExists) {
                        //$dataToDisplay[] = $data;
                        //if geo located, insert to DB
                        //first get respondent_id
                        $respondent_id = $this->findToSaveRespondent($data['user']['id'], $data['user']['name'], $data['user']['screen_name']);

                        $info = trim(str_replace('@dimanamacetid', '', $data['text']));
                        $dataToSave = [
                            //$dataToDisplay[] = [
                            'category_id' => 1,//macet
                            'user_id' => 4,//twitter robot
                            'respondent_id' => $respondent_id,
                            'weather_id' => 1,//cerah
                            'info' => $info,
                            'twitID' => $data['id'],
                            'twitCreated' => date("Y-m-d H:i:s", strtotime($data['created_at'])),//@todo this is not working, fix
                            'twitURL' => null,
                            'twitPlaceID' => $data['place']['id'],
                            'twitPlaceName' => $data['place']['name'],
                            'isTwitPlacePrecise' => 0,
                            'twitImage' => null,
                            'pinned' => 0,
                            'cleared' => 0,
                            'active' => 1
                        ];
                        // if image do exists
                        if (array_key_exists('extended_entities', $data) &&
                            array_key_exists('media', $data['extended_entities']) &&
                            $data['extended_entities']['media'][0]['type'] == 'photo'
                        ) {
                            $dataToSave['twitImage'] = $data['extended_entities']['media'][0]['media_url'];
                        }

                        // if url do exists
                        $twitURL = $this->findURLonText($info);
                        $dataToSave['twitURL'] = $twitURL;

                        if ($data['geo'] !== null) {
                            $dataToSave['lat'] = $data['geo']['coordinates'][0];
                            $dataToSave['lng'] = $data['geo']['coordinates'][1];
                            $dataToSave['isTwitPlacePrecise'] = 1;
                        } else {
                            $dataToSave['lat'] = $data['place']['bounding_box']['coordinates'][0][0][1];
                            $dataToSave['lng'] = $data['place']['bounding_box']['coordinates'][0][0][0];
                        }

                        //$dataToDisplay[] = $dataToSave;

                        //save marker
                        $marker = $this->Markers->newEntity($dataToSave);
                        $this->Markers->save($marker);
                    }
                }
            }
        }

        /*$this->set([
            'latestTwitID' => $getLatestTwitID,
            'data' => $dataToDisplay,
            'meta' => $countDataStream,
            '_serialize' => ['latestTwitID', 'data', 'meta']
        ]);*/
    }

    private function findURLonText($text)
    {
        $regex = '$\b(https?|ftp|file)://[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]$i';
        $return = null;

        preg_match_all($regex, $text, $result, PREG_PATTERN_ORDER);
        $A = $result[0];

        foreach ($A as $B) {
            $URL = $this->getRealURL($B);
            $return = $URL;
        }
        return $return;
    }

    private function getRealURL($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_USERAGENT => "spider",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_MAXREDIRS => 10,
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);
        return $header['url'];
    }

    private function findToSaveRespondent($twitterUserID, $twitterName, $twitterScreenName)
    {
        $this->Respondents = TableRegistry::get('Respondents');
        //find if id exists
        $isRespondentExists = $this->Respondents->exists(['twitUserID' => $twitterUserID, 'active' => 1]);
        //if exists return id
        if ($isRespondentExists) {
            $respondent_id = $this->Respondents->find()
                ->select(['id'])
                ->where(['twitUserID' => $twitterUserID, 'active' => 1])
                ->order(['id' => 'DESc'])
                ->first();
            //otherwise insert into table
            $respondent_id = $respondent_id['id'];
        } else {
            $dataToSave = [
                'twitUserID' => $twitterUserID,
                'name' => $twitterName,
                'contact' => '@' . $twitterScreenName,
                'active' => 1
            ];
            $respondent = $this->Respondents->newEntity($dataToSave);
            $this->Respondents->save($respondent);

            $respondent_id = $respondent->id;
        }
        return $respondent_id;
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('twits', $this->paginate($this->Twits));
        $this->set('_serialize', ['twits']);
    }

    /**
     * View method
     *
     * @param string|null $id Twit id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $twit = $this->Twits->get($id, [
            'contain' => []
        ]);
        $this->set('twit', $twit);
        $this->set('_serialize', ['twit']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $twit = $this->Twits->newEntity();
        if ($this->request->is('post')) {
            $twit = $this->Twits->patchEntity($twit, $this->request->data);
            if ($this->Twits->save($twit)) {
                $this->Flash->success(__('The twit has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The twit could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('twit'));
        $this->set('_serialize', ['twit']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Twit id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $twit = $this->Twits->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $twit = $this->Twits->patchEntity($twit, $this->request->data);
            if ($this->Twits->save($twit)) {
                $this->Flash->success(__('The twit has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The twit could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('twit'));
        $this->set('_serialize', ['twit']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Twit id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $twit = $this->Twits->get($id);
        if ($this->Twits->delete($twit)) {
            $this->Flash->success(__('The twit has been deleted.'));
        } else {
            $this->Flash->error(__('The twit could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}

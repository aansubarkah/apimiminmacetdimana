<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use TwitterAPIExchange;
use Cake\I18n\I18n;//cakephp need this to save datetime field
use Cake\I18n\Time;//cakephp need this to save datetime field
use Cake\Database\Type;//cakephp need this to save datetime field

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

    /*public $settingsTwitter = [
        'oauth_access_token' => '3555146480-EVgv9OGkcVIgxUaIoM2shbgkKAQZaJNogBE5ovF',
        'oauth_access_token_secret' => 'W78mlkW4mnNl92H7RO6eoDWtYwKU22F0sVxIyarvDqvxC',
        'consumer_key' => 'exnjHqKAXhefmLcUL93M0wBqa',
        'consumer_secret' => 'RLfNT2AeaTZZKs7RAGJCreK12rvSh98jTpGeM2l5maPYHgMQiF'
    ];

    private $baseTwitterUrl = 'https://api.twitter.com/1.1/';*/

    //private $Markers = TableRegistry::get('Markers');
    private $Markers = null;
    private $Respondents = null;

    public function location($since_id = null, $count = 100, $q = null)
    {
        $q = 'macet';
        //Gbi Ambengan, Jalan Ambengan No.48-50
        //Genteng, Kota Surabaya, Jawa Timur 60272
        //-7.256177, 112.752268
        /*
         *
         Jalan Jenderal Ahmad Yani
         Magelang Tengah, Kota Magelang, Jawa Tengah 56117
         -7.473215, 110.218126
         * */
        $geocode = '-6.171305,106.827967,100km';
        $Twitter = new TwitterAPIExchange($this->settingsTwitter);

        $url = $this->baseTwitterUrl . 'search/tweets.json';
        $getfield = '?q=' . $q;
        //if ($since_id !== null) {
        $getfield = $getfield . '&since_id=1';
        //}
        $getfield = $getfield . '&geocode=' . $geocode;
        $getfield = $getfield . '&q=' . $q;
        $getfield = $getfield . '&count=' . $count;
        $requestMethod = 'GET';

        $data = $Twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();

        $data = json_decode($data, true);
        $meta = [
            'total' => count($data['statuses'])
        ];
        //$firstDate = date('Y-m-d H:i:s', strtotime($data[0]['created_at']));

        $this->set([
            'mentions' => $data,
            'meta' => $meta,
            '_serialize' => ['mentions', 'meta']
        ]);
    }

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

        $data = json_decode($data, true);
        $meta = [
            'total' => count($data)
        ];
        $firstDate = date('Y-m-d H:i:s', strtotime($data[0]['created_at']));

        $this->set([
            'first_date' => $firstDate,
            'mentions' => $data,
            'meta' => $meta,
            '_serialize' => ['first_date', 'mentions', 'meta']
        ]);
    }

    private function getLocation($q = null)
    {
        if ($q === null) {
            $q = 'macet';
        }

        /*
         *
         Jalan Jenderal Ahmad Yani
         Magelang Tengah, Kota Magelang, Jawa Tengah 56117
         -7.473215, 110.218126
         * */
        $geocode = '-7.473215,110.218126,1000km';
        $Twitter = new TwitterAPIExchange($this->settingsTwitter);

        $url = $this->baseTwitterUrl . 'search/tweets.json';
        $getfield = '?q=' . $q;
        $getfield = $getfield . '&since_id=1';
        $count = 100;

        $getfield = $getfield . '&geocode=' . $geocode;
        $getfield = $getfield . '&q=' . $q;
        $getfield = $getfield . '&count=' . $count;
        $requestMethod = 'GET';

        $data = $Twitter->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();

        return json_decode($data, true);
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

    public function exampleTwit()
    {
        $q = 'macet';
        $dataStream = $this->getLocation($q);
        $dataToDisplay = [];
        if ($dataStream['search_metadata']['count'] > 0) {

            foreach ($dataStream['statuses'] as $data) {
                if ($data['geo'] !== null) {
                    $dataToDisplay[] = [
                        'twitTime' => date("Y-m-d H:i:s", strtotime($data['created_at'])),
                        'twitPlaceName' => $data['place']['name'],
                        'info' => $this->getThreeString($data['text'], $q),
                        'lat' => $data['geo']['coordinates'][0],
                        'lng' => $data['geo']['coordinates'][1]
                    ];
                }
            }
        }

        $meta = [
            'total' => count($dataToDisplay)
        ];

        $this->set([
            'examples' => $dataToDisplay,
            'meta'=>$meta,
            '_serialize' => ['examples','meta']
        ]);
    }

    private function getThreeString($text, $keyword)
    {
        $textArr = explode(" ", $text);
        $countTextArr = count($textArr);
        $keywordPosition = array_search($keyword, $textArr);
        $textToReturn = $textArr[$keywordPosition];

        if (($keywordPosition - 1) > 0) {
            $textToReturn = $textArr[$keywordPosition - 1] . ' ' . $textToReturn;
        }
        if (($keywordPosition + 1) < $countTextArr) {
            $textToReturn = $textToReturn . ' ' . $textArr[$keywordPosition + 1];
        }

        return trim($textToReturn);
    }

    public function mentionToDB()
    {
        $this->autoRender = false;
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
                        $created_at = date("Y-m-d H:i:s", strtotime($data['created_at']));
                        Type::build('datetime')->useLocaleParser();//cakephp need this to save datetime field
                        $dataToSave = [
                            //$dataToDisplay[] = [
                            'category_id' => 1,//macet
                            'user_id' => 4,//twitter robot
                            'respondent_id' => $respondent_id,
                            'weather_id' => 1,//cerah
                            'twitID' => $data['id'],
                            'twitTime' => new Time($created_at),//cakephp use this to save datetime field
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
                            $dataToSave['twitImage'] = $data['extended_entities']['media'][0]['media_url_https'];
                        }

                        // if url do exists
                        $twitURL = $this->findURLonText($info);
                        if ($twitURL !== null) {
                            $dataToSave['twitURL'] = $twitURL;
                            $info = str_ireplace($twitURL, "", $info);
                            $info = trim($info);
                        }
                        $dataToSave['info'] = $info;

                        // category_id and weather_id based on twit
                        $twitHashtagCategoryWeather = $this->findHashtagonText($info);
                        $dataToSave['category_id'] = $twitHashtagCategoryWeather[0];
                        $dataToSave['weather_id'] = $twitHashtagCategoryWeather[1];
                        //$dataToSave['info'] = $twitHashtagCategoryWeather[2];

                        // if get precise location
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

    // to find category_id and weather_id
    // @todo #Lapor #Tanya
    private function findHashtagonText($text)
    {
        //$newText = $text;
        $category_id = 1;//macet
        $weather_id = 1;//cerah
        preg_match_all('/#([^\s]+)/', $text, $matches);

        foreach ($matches[1] as $data) {
            $data = strtolower($data);
            switch ($data) {
            case 'padat':
                $category_id = 2;
                break;
            case 'lancar':
                $category_id = 3;
                break;
            case 'mendung':
                $weather_id = 2;
                break;
            case 'hujan deras':
                $weather_id = 3;
                break;
            case 'hujanderas':
                $weather_id = 3;
                break;
            case 'deras':
                $weather_id = 3;
                break;
            case 'gerimis':
                $weather_id = 4;
                break;
            case 'hujan':
                $weather_id = 5;
                break;
            default:
                $category_id = 1;
                $weather_id = 1;
                break;
            }

            //clean text from hashtag
            //$newText = str_ireplace($data, "", $newText);
        }
        //$newText = str_replace("#", "", $newText);
        //$newText = trim($newText);

        //return [$category_id, $weather_id, $newText];
        return [$category_id, $weather_id];
    }

    private function findURLonText($text)
    {
        $regex = '$\b(https?|ftp|file)://[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]$i';
        $return = null;

        preg_match_all($regex, $text, $result, PREG_PATTERN_ORDER);
        $return = $result[0];

        //foreach ($A as $B) {
        //    $URL = $this->getRealURL($B);
        //    $return = $URL;
        //}
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

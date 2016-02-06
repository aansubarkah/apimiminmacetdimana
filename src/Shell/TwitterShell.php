<?php
/**
 * Created by PhpStorm.
 * User: aan
 * Date: 16/09/15
 * Time: 15:20
 */
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use TwitterAPIExchange;
use Cake\ORM\TableRegistry;
use Cake\I18n\I18n;//cakephp need this to save datetime field
use Cake\I18n\Time;//cakephp need this to save datetime field
use Cake\Database\Type;//cakephp need this to save datetime field

/**
 * Twitter Shell
 * @property \App\Model\Table\MarkersTable $Markers
 * @property \App\Model\Table\RespondentsTable $Respondents
 */
class TwitterShell extends Shell
{
    public $settingsTwitter = [
        'oauth_access_token' => '3555146480-sXfyGZDtrIDdzOMd1tt8srNWUijs7nCFfeag349',
        'oauth_access_token_secret' => 'fKQN5cTbpDEvic613JtfHoVz7LC9dlSfUsP0yohuwboxY',
        'consumer_key' => 'Bu8ZMGWX8LxqR0jjbCuKTvjfG',
        'consumer_secret' => 'Fx43AKjpEksdAcG7y7SmDVH4Y2UfOVgQTzwmzSRInuPZaokGrX'
    ];

    private $baseTwitterUrl = 'https://api.twitter.com/1.1/';

    public $Markers = null;
    public $Respondents = null;

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Markers');
    }

    public function main()
    {
        //$this->out('Hello World!');
        //$this->createFile('lorem.txt','halo dunia1');
        //$dir = new Folder('app/tmp');
        //$logFile = new File('../tmp/Twitter.txt', true, 0644);
        //$logFile->write('lorem ipsum', 'w', false);
        //$dataStream = $this->getMention($latestTwitID, 800);
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
                            'twitTime' => new Time($created_at),//@todo this is not working, fix
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
                        if ($twitURL !== null) {
                            $dataToSave['twitURL'] = $twitURL;
                            $info = str_ireplace($twitURL, "", $info);
                            $info = trim($info);
                        }

                        // category_id and weather_id based on twit
                        $twitHashtagCategoryWeather = $this->findHashtagonText($info);
                        $dataToSave['category_id'] = $twitHashtagCategoryWeather[0];
                        $dataToSave['weather_id'] = $twitHashtagCategoryWeather[1];
                        $dataToSave['info'] = $twitHashtagCategoryWeather[2];

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
    }

    // to find category_id and weather_id
    // @todo #Lapor #Tanya
    private function findHashtagonText($text)
    {
        $newText = $text;
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
            $newText = str_ireplace($data, "", $newText);
        }
        $newText = str_replace("#", "", $newText);
        $newText = trim($newText);

        return [$category_id, $weather_id, $newText];
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
}
//cronjob
//php -c /home/dmmctcom/public_html/php.ini cd /home/dmmctcom/public_html/apimimin && bin/cake twitter > /home/dmmctcom/public_html/apimimin/tmp/logs/cron_logs.txt 2>&1
//wget http://apimimin.dimanamacet.com/twits/mentionToDB > /home/dmmctcom/public_html/apimimin/tmp/logs/cron_logs.txt 2>&1
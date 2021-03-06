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
 * @property \App\Model\Table\SourcesTable $Sources
 */
class TwitterShell extends Shell
{
    public $settingsTwitter = [
        'oauth_access_token' => '3555146480-EVgv9OGkcVIgxUaIoM2shbgkKAQZaJNogBE5ovF',
        'oauth_access_token_secret' => 'W78mlkW4mnNl92H7RO6eoDWtYwKU22F0sVxIyarvDqvxC',
        'consumer_key' => 'exnjHqKAXhefmLcUL93M0wBqa',
        'consumer_secret' => 'RLfNT2AeaTZZKs7RAGJCreK12rvSh98jTpGeM2l5maPYHgMQiF'
    ];

    public $settingsTwitterSurabaya = [
        'oauth_access_token' => '3517023912-QTOGN1gw8l8yzOUsZeK1HYQU0erXLrmeZz2KLX5',
        'oauth_access_token_secret' => 'cSxXX9ap0x6qbeeVsGnQyAtNuYFNVqj8MoiB39gIPTPhA',
        'consumer_key' => 'lyxul6MCMZE6plA9S1rm4ypgn',
        'consumer_secret' => 'EnKhl8ZI7VjZZkvJukemCUQdsTi5mP6KXR3vEUTCFFoZ3vC8AO'
    ];

    public $baseTwitterUrl = 'https://api.twitter.com/1.1/';

    public $Markers = null;
    public $Respondents = null;

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Markers');
    }

    public function main()
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
            $latestTwitID = 1;
        }

        // second grab twit
        $dataStream = $this->getMention($latestTwitID, 800);
        $countDataStream = count($dataStream);

        $dataToDisplay = [];
        // @todo better to return no data message after
        if ($countDataStream > 0) {
            foreach ($dataStream as $data) {
                if ($data['user']['id'] !== 3458271384) { // nasi mandi user
                    if ($data['place'] !== null) {
                        $isTwitExists = $this->Markers->exists(['twitID' => $data['id'], 'active' => 1]);
                        if (!$isTwitExists) {
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
                            //$twitHashtagCategoryWeather = $this->findHashtagonText($info);
                            $twitHashtagCategoryWeather = $this->findHashtagonText($info, $data['entities']['hashtags']);
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

                            // do retweet
                            $this->retweet($data['id']);
                        }
                    }
                }
            }
        }
    }

    // to find category_id and weather_id
    // @todo #Lapor #Tanya
    private function findHashtagonText($text, $hashtags)
    {
        $newText = $text;
        //$newText = $text;
        $category_id = 1;//macet
        $weather_id = 1;//cerah
        //preg_match_all('/#([^\s]+)/', $text, $matches);*/

        //foreach ($matches[1] as $data) {
        foreach ($hashtags as $data) {
            $data = strtolower($data['text']);
            switch ($data) {
            case 'padat':
                $category_id = 2;
                break;
            case 'lancar':
                $category_id = 3;
                break;
            case 'kecelakaan':
                $category_id = 4;
                break;
            case 'laka':
                $category_id = 4;
                break;
            case 'waspada':
                $category_id = 5;
                break;
            case 'ramai':
                $category_id = 6;
                break;
            case 'mendung':
                $weather_id = 2;
                break;
            case 'hujan':
                $weather_id = 3;
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
                ->where(['twitUserID' => $twitterUserID])
                ->order(['id' => 'DESc'])
                ->first();
            //otherwise insert into table
            $respondent_id = $respondent_id['id'];
        } else {
            $dataToSave = [
                'twitUserID' => $twitterUserID,
                'name' => $twitterName,
                'contact' => '@' . $twitterScreenName,
                'isOfficial' => 0,
                'active' => 1
            ];
            $respondent = $this->Respondents->newEntity($dataToSave);
            $this->Respondents->save($respondent);

            $respondent_id = $respondent->id;
        }
        return $respondent_id;
    }

    /**
     * Retweet Method
     *
     * @return void
     */
    private function retweet($twitID = null) {
        if (!empty($twitID)) {
            $Twitter = new TwitterAPIExchange($this->settingsTwitter);
            $url = $this->baseTwitterUrl . 'statuses/retweet/';
            $url = $url . $twitID . '.json';
            $requestMethod = 'POST';
            $postfield = [];

            $exec = $Twitter->setPostfields($postfield)
                ->buildOauth($url, $requestMethod)
                ->performRequest();
        }
    }

    /**
     * Get Timeline Method
     *
     **/
    private function getTimeline($latestTwitID = 0, $howManyTweets = 200) {
        $Twitter = new TwitterAPIExchange($this->settingsTwitterSurabaya);

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

    public function test(){
        $this->Sources = TableRegistry::get('Sources');

        $respondent = $this->Sources->Respondents->find()
            ->contain(['Regions'])
            ->select(['Respondents.id', 'Respondents.region_id', 'Regions.lat', 'Regions.lng', 'Regions.name'])
            ->where(['isOfficial' => 1, 'contact' => '@e100ss'])
            ->first();
        $this->out($respondent['id']);
        $this->out($respondent['region_id']);
        $this->out($respondent['region']['lat']);
    }

    public function timeLine() {
        $this->Sources = TableRegistry::get('Sources');
        // first get the latest twitID from DB
        $getLatestTwitID = $this->Sources->find()
            ->select(['twitID'])
            //->where(['active' => true])
            ->order(['twitID' => 'DESC'])
            ->first();

        if ($getLatestTwitID['twitID'] > 0) {
            $latestTwitID = $getLatestTwitID['twitID'] + 1;
        } else {
            $latestTwitID = 1;
        }
        $dataStream = $this->getTimeline($latestTwitID, 200);
        $countDataStream = count($dataStream);

        if ($countDataStream > 0) {
            foreach ($dataStream as $datum) {
                if ($datum['user']['id'] !== 3555146480) {// dimanamacetid twitter user
                    $respondent = $this->Sources->Respondents->find()
                        ->contain(['Regions'])
                        ->select(['Respondents.id', 'Respondents.region_id', 'Regions.lat', 'Regions.lng', 'Regions.name'])
                        ->where(['isOfficial' => 1, 'contact' => '@' . $datum['user']['screen_name']])
                        ->first();
                    $respondent_id = $respondent['id'];

                    if (!empty($respondent_id)) {
                        $info = $datum['text'];
                        $created_at = date("Y-m-d H:i:s", strtotime($datum['created_at']));
                        Type::build('datetime')->useLocaleParser();//cakephp need this to save datetime field
                        $dataToSave = [
                            'respondent_id' => $respondent_id,
                            'region_id' => $respondent['region_id'],
                            'regionName' => $respondent['region']['name'],
                            'regionLat' => $respondent['region']['lat'],
                            'regionLng' => $respondent['region']['lng'],
                            'placeName' => null,
                            'lat' => 0,
                            'lng' => 0,
                            'twitID' => $datum['id'],
                            'twitTime' => new Time($created_at),
                            'twitUserID' => $datum['user']['id'],
                            'twitUserScreenName' => $datum['user']['screen_name'],
                            'info' => $datum['text'],
                            'url' => null,
                            'media' => null,
                            'mediaWidth' => 0,
                            'mediaHeight' => 0,
                            'guessPlaceName' => null,
                            'guessPlaceID' => 0,
                            'guessPlaceLat' => 0,
                            'guessPlaceLng' => 0,
                            'guessCategoryName' => 'Lancar',
                            'guessCategoryID' => 3,
                            'guessWeatherName' => 'Cerah',
                            'guessWeatherID' => 1,
                            'isRelevant' => 1,
                            'isGuessPlaceRight' => 0,
                            'isGuessCategoryRight' => 0,
                            'isGuessWeatherRight' => 0,
                            'isImported' => 0,
                            'active' => 1
                        ];
                        // if image do exists
                        if (array_key_exists('entities', $datum) &&
                            array_key_exists('media', $datum['entities']) &&
                            $datum['entities']['media'][0]['type'] == 'photo'
                        ) {
                            $dataToSave['media'] = $datum['entities']['media'][0]['media_url_https'];
                            $dataToSave['mediaWidth'] = $datum['entities']['media'][0]['sizes']['small']['w'];
                            $dataToSave['mediaHeight'] = $datum['entities']['media'][0]['sizes']['small']['h'];
                        }
                        // if url do exists
                        if (array_key_exists('entities', $datum) &&
                            array_key_exists('urls', $datum['entities'])
                        ) {
                            $dataToSave['url'] = $datum['entities']['urls'][0]['url'];
                        }
                        /*$twitURL = $this->findURLonText($info);
                        if ($twitURL !== null) {
                            $dataToSave['url'] = $twitURL;
                            $info = str_ireplace($twitURL, "", $info);
                            $info = trim($info);
                        }*/
                        //$dataToSave['info'] = $info;

                        // if get precise location
                        if ($datum['geo'] !== null) {
                            $dataToSave['lat'] = $datum['geo']['coordinates'][0];
                            $dataToSave['lng'] = $datum['geo']['coordinates'][1];
                        }

                        // save twit to db
                        $source = $this->Sources->newEntity($dataToSave);
                        if ($this->Sources->save($source)) {
                            $this->out(date('Y-m-d H:i:s') . ' succeed');
                        } else {
                            $this->out(date('Y-m-d H:i:s') . ' error occured');
                        }
                    }
                }
            }// foreach end here
        }
    }
}

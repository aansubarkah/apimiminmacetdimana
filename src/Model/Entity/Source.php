<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Source Entity.
 *
 * @property int $id
 * @property int $respondent_id
 * @property \App\Model\Entity\Respondent $respondent
 * @property int $region_id
 * @property \App\Model\Entity\Region $region
 * @property string $regionName
 * @property float $regionLat
 * @property float $regionLng
 * @property string $placeName
 * @property int $category_id
 * @property \App\Model\Entity\Category $category
 * @property string $categoryName
 * @property int $weather_id
 * @property \App\Model\Entity\Weather $weather
 * @property string $weatherName
 * @property float $lat
 * @property float $lng
 * @property int $twitID
 * @property \Cake\I18n\Time $twitTime
 * @property int $twitUserID
 * @property string $twitUserScreenName
 * @property string $info
 * @property string $url
 * @property string $media
 * @property int $mediaWidth
 * @property int $mediaHeight
 * @property string $guessPlaceName
 * @property int $guessPlaceID
 * @property float $guessPlaceLat
 * @property float $guessPlaceLng
 * @property string $guessCategoryName
 * @property int $guessCategoryID
 * @property string $guessWeatherName
 * @property int $guessWeatherID
 * @property bool $isRelevant
 * @property bool $isGuessPlaceRight
 * @property bool $isGuessCategoryRight
 * @property bool $isGuessWeatherName
 * @property bool $isImported
 * @property bool $active
 */
class Source extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     *
     * Virtual Field
     */
    protected $_virtual = ['twitid_str'];

    protected function _getTwitidStr()
    {
        $bigInt = gmp_init($this->_properties['twitID']);
        $bigIntStr = gmp_strval($bigInt);
        return $bigIntStr;
    }
}

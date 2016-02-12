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
 * @property float $lat
 * @property float $lng
 * @property int $twitID
 * @property \Cake\I18n\Time $twitTime
 * @property int $twitUserID
 * @property string $twitUserScreenName
 * @property string $info
 * @property string $url
 * @property string $media
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
}

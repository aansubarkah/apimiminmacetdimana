<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Marker Entity.
 *
 * @property int $id
 * @property int $category_id
 * @property \App\Model\Entity\Category $category
 * @property int $user_id
 * @property \App\Model\Entity\User $user
 * @property int $respondent_id
 * @property \App\Model\Entity\Respondent $respondent
 * @property int $weather_id
 * @property \App\Model\Entity\Weather $weather
 * @property float $lat
 * @property float $lng
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property string $info
 * @property int $twitID
 * @property string $twitPlaceID
 * @property \Cake\I18n\Time $twitTime
 * @property string $twitURL
 * @property string $twitPlaceName
 * @property bool $isTwitPlacePrecise
 * @property string $twitImage
 * @property bool $pinned
 * @property bool $cleared
 * @property bool $active
 */
class Marker extends Entity
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

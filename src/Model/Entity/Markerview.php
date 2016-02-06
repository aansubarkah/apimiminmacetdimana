<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Markerview Entity.
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
 * @property int $twitUserID
 * @property int $twitID
 * @property \Cake\I18n\Time $twitTime
 * @property string $twitURL
 * @property string $twitPlaceID
 * @property string $twitPlaceName
 * @property bool $isTwitPlacePrecise
 * @property string $twitImage
 * @property bool $pinned
 * @property bool $cleared
 * @property bool $active
 * @property string $category_name
 * @property string $username
 * @property string $user_email
 * @property string $respondent_name
 * @property string $respondent_contact
 * @property string $weather_name
 * @property string $place_name
 * @property int $isTwitUserIDExist
 * @property int $isTwitExist
 * @property int $isTwitImageExist
 * @property int $isTwitURLExist
 * @property int $isPlaceNameExist
 */
class Markerview extends Entity
{

}

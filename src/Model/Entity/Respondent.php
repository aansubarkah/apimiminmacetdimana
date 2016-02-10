<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Respondent Entity.
 *
 * @property int $id
 * @property int $twitUserID
 * @property string $name
 * @property string $contact
 * @property bool $active
 * @property \App\Model\Entity\Marker[] $markers
 * @property \App\Model\Entity\Markerview[] $markerviews
 * @property \App\Model\Entity\Region $region
 */
class Respondent extends Entity
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

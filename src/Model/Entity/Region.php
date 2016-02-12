<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Region Entity.
 *
 * @property int $id
 * @property float $lat
 * @property float $lng
 * @property string $name
 * @property bool $active
 * @property \App\Model\Entity\User[] $users
 * @property \App\Model\Entity\Respondent[] $respondents
 * @property \App\Model\Entity\Source[] $sources
 */
class Region extends Entity
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

<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Regency Entity.
 *
 * @property int $id
 * @property int $state_id
 * @property \App\Model\Entity\State $state
 * @property int $hierarchy_id
 * @property \App\Model\Entity\Hierarchy $hierarchy
 * @property int $region_id
 * @property \App\Model\Entity\Region $region
 * @property string $name
 * @property float $lat
 * @property float $lng
 * @property bool $active
 */
class Regency extends Entity
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

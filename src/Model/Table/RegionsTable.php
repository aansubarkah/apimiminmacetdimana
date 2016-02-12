<?php
namespace App\Model\Table;

use App\Model\Entity\Region;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Regions Model
 *
 * @property \Cake\ORM\Association\HasMany $Users
 * @property \Cake\ORM\Association\HasMany $Respondents
 * @property \Cake\ORM\Association\HasMany $Sources
 */
class RegionsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('regions');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->hasMany('Users', [
            'foreignKey' => 'region_id'
        ]);
        $this->hasMany('Respondents', [
            'foreignKey' => 'region_id'
        ]);
        $this->hasMany('Sources', [
            'foreignKey' => 'region_id'
        ]);

    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->add('lat', 'valid', ['rule' => 'numeric'])
            ->requirePresence('lat', 'create')
            ->notEmpty('lat');

        $validator
            ->add('lng', 'valid', ['rule' => 'numeric'])
            ->requirePresence('lng', 'create')
            ->notEmpty('lng');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->add('active', 'valid', ['rule' => 'boolean'])
            ->requirePresence('active', 'create')
            ->notEmpty('active');

        return $validator;
    }
}

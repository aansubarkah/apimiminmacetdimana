<?php
namespace App\Model\Table;

use App\Model\Entity\Respondent;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Respondents Model
 *
 * @property \Cake\ORM\Association\HasMany $Markers
 * @property \Cake\ORM\Association\HasMany $Markerviews
 * @property \Cake\ORM\Association\HasMany $Sources
 * @property \Cake\ORM\Association\BelongsTo $Regions
 */
class RespondentsTable extends Table
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

        $this->table('respondents');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->belongsTo('Regions', [
            'foreignKey' => 'region_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Markers', [
            'foreignKey' => 'respondent_id'
        ]);
        $this->hasMany('Markerviews', [
            'foreignKey' => 'respondent_id'
        ]);
        $this->hasMany('Sources', [
            'foreignKey' => 'respondent_id'
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
            ->allowEmpty('id', 'create');

        $validator
            ->allowEmpty('twitUserID');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('contact', 'create')
            ->notEmpty('contact');

        $validator
            ->add('isOfficial', 'valid', ['rule' => 'boolean'])
            ->requirePresence('isOfficial', 'create')
            ->notEmpty('isOfficial');

        $validator
            ->add('active', 'valid', ['rule' => 'boolean'])
            ->requirePresence('active', 'create')
            ->notEmpty('active');

        return $validator;
    }
}

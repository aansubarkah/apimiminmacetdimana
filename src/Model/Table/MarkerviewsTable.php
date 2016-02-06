<?php
namespace App\Model\Table;

use App\Model\Entity\Markerview;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Markerviews Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Respondents
 * @property \Cake\ORM\Association\BelongsTo $Weather
 */
class MarkerviewsTable extends Table
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

        $this->table('markerviews');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Respondents', [
            'foreignKey' => 'respondent_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Weather', [
            'foreignKey' => 'weather_id',
            'joinType' => 'INNER'
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
            ->requirePresence('id', 'create')
            ->notEmpty('id');

        $validator
            ->add('lat', 'valid', ['rule' => 'numeric'])
            ->requirePresence('lat', 'create')
            ->notEmpty('lat');

        $validator
            ->add('lng', 'valid', ['rule' => 'numeric'])
            ->requirePresence('lng', 'create')
            ->notEmpty('lng');

        $validator
            ->allowEmpty('info');

        $validator
            ->allowEmpty('twitUserID');

        $validator
            ->allowEmpty('twitID');

        $validator
            ->add('twitTime', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('twitTime');

        $validator
            ->allowEmpty('twitURL');

        $validator
            ->allowEmpty('twitPlaceID');

        $validator
            ->allowEmpty('twitPlaceName');

        $validator
            ->add('isTwitPlacePrecise', 'valid', ['rule' => 'boolean'])
            ->requirePresence('isTwitPlacePrecise', 'create')
            ->notEmpty('isTwitPlacePrecise');

        $validator
            ->allowEmpty('twitImage');

        $validator
            ->add('pinned', 'valid', ['rule' => 'boolean'])
            ->requirePresence('pinned', 'create')
            ->notEmpty('pinned');

        $validator
            ->add('cleared', 'valid', ['rule' => 'boolean'])
            ->requirePresence('cleared', 'create')
            ->notEmpty('cleared');

        $validator
            ->add('active', 'valid', ['rule' => 'boolean'])
            ->requirePresence('active', 'create')
            ->notEmpty('active');

        $validator
            ->allowEmpty('category_name');

        $validator
            ->allowEmpty('username');

        $validator
            ->allowEmpty('user_email');

        $validator
            ->allowEmpty('respondent_name');

        $validator
            ->allowEmpty('respondent_contact');

        $validator
            ->allowEmpty('weather_name');

        $validator
            ->allowEmpty('place_name');

        $validator
            ->add('isTwitUserIDExist', 'valid', ['rule' => 'numeric'])
            ->requirePresence('isTwitUserIDExist', 'create')
            ->notEmpty('isTwitUserIDExist');

        $validator
            ->add('isTwitExist', 'valid', ['rule' => 'numeric'])
            ->requirePresence('isTwitExist', 'create')
            ->notEmpty('isTwitExist');

        $validator
            ->add('isTwitImageExist', 'valid', ['rule' => 'numeric'])
            ->requirePresence('isTwitImageExist', 'create')
            ->notEmpty('isTwitImageExist');

        $validator
            ->add('isTwitURLExist', 'valid', ['rule' => 'numeric'])
            ->requirePresence('isTwitURLExist', 'create')
            ->notEmpty('isTwitURLExist');

        $validator
            ->add('isPlaceNameExist', 'valid', ['rule' => 'numeric'])
            ->requirePresence('isPlaceNameExist', 'create')
            ->notEmpty('isPlaceNameExist');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['username']));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['respondent_id'], 'Respondents'));
        $rules->add($rules->existsIn(['weather_id'], 'Weather'));
        return $rules;
    }
}

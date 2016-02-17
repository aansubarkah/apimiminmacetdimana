<?php
namespace App\Model\Table;

use App\Model\Entity\Source;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Sources Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Respondents
 * @property \Cake\ORM\Association\BelongsTo $Regions
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Weathers
 */
class SourcesTable extends Table
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

        $this->table('sources');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Respondents', [
            'foreignKey' => 'respondent_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Regions', [
            'foreignKey' => 'region_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Weathers', [
            'foreignKey' => 'weather_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
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
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('regionName', 'create')
            ->notEmpty('regionName');

        $validator
            ->add('regionLat', 'valid', ['rule' => 'numeric'])
            ->requirePresence('regionLat', 'create')
            ->notEmpty('regionLat');

        $validator
            ->add('regionLng', 'valid', ['rule' => 'numeric'])
            ->requirePresence('regionLng', 'create')
            ->notEmpty('regionLng');

        $validator
            ->allowEmpty('placeName', 'create');

        $validator
            ->allowEmpty('categoryName', 'create');

        $validator
            ->allowEmpty('weatherName', 'create');

        $validator
            ->add('lat', 'valid', ['rule' => 'numeric'])
            ->requirePresence('lat', 'create')
            ->notEmpty('lat');

        $validator
            ->add('lng', 'valid', ['rule' => 'numeric'])
            ->requirePresence('lng', 'create')
            ->notEmpty('lng');

        $validator
            ->requirePresence('twitID', 'create')
            ->notEmpty('twitID');

        $validator
            ->add('twitTime', 'valid', ['rule' => 'datetime'])
            ->requirePresence('twitTime', 'create')
            ->notEmpty('twitTime');

        $validator
            ->requirePresence('twitUserID', 'create')
            ->notEmpty('twitUserID');

        $validator
            ->requirePresence('twitUserScreenName', 'create')
            ->notEmpty('twitUserScreenName');

        $validator
            ->requirePresence('info', 'create')
            ->notEmpty('info');

        $validator
            ->allowEmpty('url', 'create');

        $validator
            ->allowEmpty('media', 'create');

        $validator
            ->add('isImported', 'valid', ['rule' => 'boolean'])
            ->requirePresence('isImported', 'create')
            ->notEmpty('isImported');

        $validator
            ->add('active', 'valid', ['rule' => 'boolean'])
            ->requirePresence('active', 'create')
            ->notEmpty('active');

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
        $rules->add($rules->existsIn(['respondent_id'], 'Respondents'));
        return $rules;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'alternative';
    }
}

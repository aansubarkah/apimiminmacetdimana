<?php
namespace App\Shell;

use Cake\Console\Shell;
//use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Network\Email\Email;

/**
 * Report shell command.
 */
class ReportShell extends Shell
{
    public $Users = null;

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Users');
    }

    /**
     * main() method.
     *
     * @return bool|int Success or error code.
     */
    public function main()
    {
        $this->out('Hello World This is Report');
    }

    public function activitiesToday() {
        $this->Users = TableRegistry::get('Users');

        $inputers = $this->Users->find();
        $inputers->where(['active' => 1]);
        $yesterday = date('Y-m-d', strtotime('-1 days'));

        foreach($inputers as $inputer) {
            $isTodayActivityInserted = $this->Users->Activities->find()
                ->where([
                    'user_id' => $inputer['id'],
                    'DATE(occured)' => $yesterday,
                    'active' => 1
                ])
                ->count();

            if($isTodayActivityInserted < 1) {
                $todayActivity = $this->Users->Markers->find()
                    ->where([
                        'user_id' => $inputer['id'],
                        'DATE(created)' => $yesterday,
                        'active' => 1
                    ])
                    ->count();

                $dataToSave = [
                    'user_id' => $inputer['id'],
                    'occured' => $yesterday,
                    'value' => $todayActivity,
                    'active' => 1
                ];
                $activity = $this->Users->Activities->newEntity($dataToSave);
                $this->Users->Activities->save($activity);
            }
        }
    }

    public function emailToManager() {
        //$this->out('Hello Manager');
        $email = new Email('default');
        $email->from(['admin@dimanamacet.com' => 'Administrator dimanamacet.com'])
            ->to('aansubarkah@gmail.com')
            ->subject('Daily Activity')
            ->send('Lorem Ipsum DOlor sit Amet');
        $this->out('Success');
    }

    public function test() {
        $dataToSave = [
            'user_id' => 1,
            'occured' => date('Y-m-d', strtotime('-1 days')),
            'value' => 1,
            'active' => 0
        ];

        $activity = $this->Users->Activities->newEntity($dataToSave);
        $this->Users->Activities->save($activity);
    }

    /*public function add() {
        // get all manager (inputer) ids
        $managers = $this->Users->find();
        $allManagers = [];
        $dataID = 0;

        foreach ($managers as $manager) {
            $id = $manager['id'];

            for($i = 1; $i < 25; $i++) {
                $days = '-' . ($i) . ' days';
                $date = date('Y-m-d', strtotime($days));
                $userRowsCount = $this->Users->Markers->find()
                    ->where([
                        'AND' => [
                            ['Markers.user_id' => $id],
                            ['Date(Markers.created)' => $date],
                            ['Markers.active' => 1]
                        ]
                    ])
                    ->count();

                $dataToSave = [
                    'user_id' => $id,
                    'occured' => $date,
                    'value' => $userRowsCount,
                    'active' => 1
                ];

                $activity = $this->Users->Activities->newEntity($dataToSave);
                $this->Users->Activities->save($activity);
            }
        }
    }*/
}

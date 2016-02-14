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

        foreach($inputers as $inputer) {
            $isTodayActivityInserted = $this->Users->Activities->find()
                ->where([
                    'user_id' => $inputer['id'],
                    'DATE(created)' => date('Y-m-d'),
                    'active' => 1
                ])
                ->count();

            if($isTodayActivityInserted < 1) {
                $todayActivity = $this->Users->Markers->find()
                    ->where([
                        'user_id' => $inputer['id'],
                        'DATE(created)' => date('Y-m-d'),
                        'active' => 1
                    ])
                    ->count();

                $dataToSave = [
                    'user_id' => $inputer['id'],
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
}

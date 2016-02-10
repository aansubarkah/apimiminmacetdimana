<?php
namespace App\Shell;

use Cake\Console\Shell;
//use Cake\Mailer\Email;
use Cake\Network\Email\Email;

/**
 * Report shell command.
 */
class ReportShell extends Shell
{
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Markers');
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

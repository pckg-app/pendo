<?php namespace Pckg\Pendo\Form;

use Pckg\Htmlbuilder\Element\Form;
use Pckg\Htmlbuilder\Element\Form\ResolvesOnRequest;

class Configure extends Form implements ResolvesOnRequest
{

    public function initFields()
    {
        $this->addFile('p12')->setLabel('Client .p12 certificate');
        $this->addFile('pem')->setLabel('Client .pem certificate');
        $this->addFile('server')->setLabel('Server certificate');
        $this->addPassword('password')->setLabel('Password');
        $this->addSubmit('submit')->setValue('Save data');

        return $this;
    }

}
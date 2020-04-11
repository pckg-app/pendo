<?php namespace Pckg\Pendo\Form;

use Pckg\Htmlbuilder\Element\Form;

class RegisterUser extends Form implements Form\ResolvesOnRequest
{

    public function initFields()
    {
        $this->addEmail('email')->required();

        return $this;
    }

}
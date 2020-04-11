<?php namespace Pckg\Pendo\Form;

use Pckg\Htmlbuilder\Element\Form;

class RegisterAppKey extends Form implements Form\ResolvesOnRequest
{

    public function initFields()
    {
        $this->addSelect('app_id')->required();

        return $this;
    }

}
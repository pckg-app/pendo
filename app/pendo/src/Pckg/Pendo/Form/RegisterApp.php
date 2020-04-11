<?php namespace Pckg\Pendo\Form;

use Pckg\Htmlbuilder\Element\Form;

class RegisterApp extends Form implements Form\ResolvesOnRequest
{

    public function initFields()
    {
        $this->addText('title')->required();
        $this->addSelect('company_id')->required();
        $this->addSelect('user_id')->required();

        return $this;
    }

}
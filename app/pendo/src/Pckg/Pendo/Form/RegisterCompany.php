<?php namespace Pckg\Pendo\Form;

use Pckg\Htmlbuilder\Element\Form;

class RegisterCompany extends Form implements Form\ResolvesOnRequest
{

    public function initFields()
    {
        $this->addText('short_name')->required();
        $this->addText('long_name')->required();
        $this->addText('vat_number')->required();
        $this->addText('business_number')->required();
        $this->addText('country_code')->required()->max(2);
        $this->addText('address_line1')->required();
        $this->addText('address_line2')->required();
        $this->addDate('incorporated_at')->required();

        $this->addSelect('user_id')->required();

        return $this;
    }

}
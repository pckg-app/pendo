<?php namespace Pckg\Pendo\Service\Fiscalization;

class Business
{

    protected $id;

    protected $taxNumber;

    protected $validityDate;

    protected $electronicDeviceId;

    public function __construct($id, $taxNumber, $validityDate, $electronicDeviceId)
    {
        $this->id = $id;
        $this->taxNumber = $taxNumber;
        $this->validityDate = $validityDate;
        $this->electronicDeviceId = $electronicDeviceId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTaxNumber($slice = true)
    {
        if (!in_array(substr($this->taxNumber, 0, 2), ['SI', 'HR'])) {
            return $this->taxNumber;
        }

        return substr($this->taxNumber, 2);
    }

    public function getValidityDate()
    {
        return $this->validityDate;
    }

    public function getElectronicDeviceId()
    {
        return $this->electronicDeviceId;
    }

    public function setElectronicDeviceId($electronicDeviceId)
    {
        $this->electronicDeviceId = $electronicDeviceId;

        return $this;
    }

}

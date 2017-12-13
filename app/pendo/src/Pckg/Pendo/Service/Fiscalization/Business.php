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

    public function getTaxNumber($slice = false)
    {
        return $slice
            ? substr($this->taxNumber, 2)
            : $this->taxNumber;
    }

    public function getValidityDate()
    {
        return $this->validityDate;
    }

    public function getElectronicDeviceId()
    {
        return $this->electronicDeviceId;
    }

}
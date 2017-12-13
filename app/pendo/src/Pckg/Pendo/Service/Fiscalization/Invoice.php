<?php namespace Pckg\Pendo\Service\Fiscalization;

class Invoice
{

    protected $invoiceNumber;

    protected $invoiceAmount;

    protected $paymentAmount;

    protected $issueDateTime;

    public function __construct($invoiceNumber, $invoiceAmount, $paymentAmount, $issueDateTime)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceAmount = $invoiceAmount;
        $this->paymentAmount = $paymentAmount;
        $this->issueDateTime = $issueDateTime;
    }

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceAmount()
    {
        return str_replace(',', '', $this->invoiceAmount);
    }

    public function getPaymentAmount()
    {
        return str_replace(',', '', $this->paymentAmount);
    }

    public function getIssueDateTime()
    {
        return $this->issueDateTime;
    }

}
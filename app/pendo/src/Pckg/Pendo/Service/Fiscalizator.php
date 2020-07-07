<?php namespace Pckg\Pendo\Service;

use Pckg\Pendo\Entity\Fiscalizations;
use Pckg\Pendo\Record\Company;
use Pckg\Pendo\Service\Fiscalization\AbstractService;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Pckg\Pendo\Service\Fiscalization\Service\Furs;
use Pckg\Pendo\Service\Fiscalization\Service\Pendo;
use Pckg\Pendo\Service\Fiscalization\Service\Purh;

class Fiscalizator
{

    protected $order;

    protected $price;

    protected $platformId;

    protected $saveZoi;

    /**
     * @var \Pckg\Pendo\Record\Fiscalization
     */
    protected $fiscalizationRecord;

    /**
     * @var Business
     */
    protected $fiscalizationBusiness;

    protected $invoiceData;

    public function __construct(Company $company, array $invoiceData)
    {
        $this->config = $company->getFiscalizationConfig();
        $this->fiscalizationBusiness = $company->createFiscalizationBusiness($invoiceData['business'],
                                                                             $invoiceData['device']);
        $this->invoiceData = $invoiceData;
    }

    public function createInvoice()
    {
        return new Invoice(
            $this->fiscalizationRecord->furs_id,
            number_format($this->order['total'], 2),
            number_format($this->order['payment'], 2),
            date('Y-m-d') . 'T' . date('H:i:s')
        );
    }

    /**
     * @return Furs|Purh|Pendo
     */
    public function createServiceFromBusiness()
    {
        $handler = $this->invoiceData['handler'] ?? null;

        if ($handler == 'pendo') {
            return new Pendo($this->config, $this->fiscalizationBusiness);
        } elseif ($handler == 'furs') {
            return new Furs($this->config, $this->fiscalizationBusiness);
        } elseif ($handler == 'purh') {
            return new Purh($this->config, $this->fiscalizationBusiness);
        }

        throw new \Exception('Handler is required');
    }

    public function fiscalize()
    {
        /**
         * Create fiscalization business and service service based on company country (furs or purh).
         */
        $fiscalizationService = $this->createServiceFromBusiness();

        /**
         * We're fiscalizing some bill, so we need to set some data.
         */
        $this->setOrder(only($this->invoiceData, ['payment', 'total', 'datetime', 'identifier', 'platform']));

        /**
         * Create new fiscalization record for fiscalization business.
         */
        $this->fiscalizationRecord = $this->createFiscalizationRecord($fiscalizationService);

        /**
         * Create fiscalization invoice.
         */
        $fiscalizationService->setInvoice($this->createInvoice());

        /**
         * Create invoice request.
         */
        $fiscalizationService->createInvoiceMsg($this->invoiceData);
        $fiscalizationService->postXml();

        /**
         * Store some data.
         */
        $data = [
            // 'qr'  => 'generateQR', // Generate QR code.
            'eor' => 'getEOR', // Set EOR code, which is always the same for same bill.
            'zoi' => 'getZOI', // ZOU changes based on date of confirmation and other properties.
        ];
        foreach ($data as $key => $method) {
            try {
                if ($value = $fiscalizationService->{$method}()) {
                    $this->fiscalizationRecord->{$key} = $value;
                }
            } catch (\Throwable $e) {
                $env = env();
                if (method_exists($env, 'reportToRollbar')) {
                    $env->reportToRollbar($e);
                    continue;
                }

                throw $e;
            }
        }

        $this->fiscalizationRecord->save();

        return $this;
    }

    public function createFiscalizationService()
    {
        /**
         * Create business.
         */
        $business = $this->getFiscalizationBusiness();

        /**
         * Get or create FURS invoice number.
         */
        $this->fiscalizationRecord = $this->createFiscalizationRecord($business);

        /**
         * Create invoice.
         */
        $invoice = $this->createInvoice();

        return $this->order->company->createFiscalizationService($business, $invoice);
    }

    public function createFiscalizationBusiness()
    {
        $this->fiscalizationBusiness = $this->order->company->createFiscalizationBusiness();

        return $this;
    }

    public function getFiscalizationBusiness()
    {
        if (!$this->fiscalizationBusiness) {
            $this->createFiscalizationBusiness();
        }

        return $this->fiscalizationBusiness;
    }

    public function createFiscalizationRecord(AbstractService $fiscalizationService)
    {
        $fiscalizationRecord = (new Fiscalizations())->getOrCreateFromOrder($this->invoiceData['identifier'],
                                                                            $this->fiscalizationBusiness,
                                                                            $this->invoiceData['platform'],
                                                                            $this->invoiceData['mode'] ?? 'prod',
                                                                            $fiscalizationService
        );
        $fiscalizationRecord->invoice = $this->invoiceData['total'];
        $fiscalizationRecord->payment = $this->invoiceData['payment'];

        return $fiscalizationRecord;
    }

    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function setPlatformId($platformId)
    {
        $this->platformId = $platformId;

        return $this;
    }

    public function setSaveZoi($saveZoi = true)
    {
        $this->saveZoi = $saveZoi;

        return $this;
    }

    public function getFiscalizationRecord()
    {
        return $this->fiscalizationRecord;
    }
}
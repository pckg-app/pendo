<?php namespace Pckg\Pendo\Migration;

use Pckg\Migration\Migration;

class CreateFiscalizationTables extends Migration
{

    public function up()
    {
        $fiscalizations = $this->table('fiscalizations');
        $fiscalizations->integer('order_id');
        $fiscalizations->varchar('platform_id');
        $fiscalizations->datetime('requested_at');
        $fiscalizations->integer('furs_id');
        $fiscalizations->varchar('business_id');
        $fiscalizations->varchar('electronic_device_id');
        $fiscalizations->varchar('business_tax_number');
        $fiscalizations->varchar('type');
        $fiscalizations->decimal('invoice');
        $fiscalizations->decimal('payment');
        $fiscalizations->varchar('eor');
        $fiscalizations->varchar('zoi');
        $fiscalizations->integer('next_id');
        $fiscalizations->datetime('requested_at');

        $this->save();
    }

}
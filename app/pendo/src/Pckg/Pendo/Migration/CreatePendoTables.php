<?php namespace Pckg\Pendo\Migration;

use Pckg\Api\Migration\CreateApiTables;
use Pckg\Auth\Migration\CreateAuthTables;
use Pckg\Migration\Migration;

class CreatePendoTables extends Migration
{

    public function dependencies()
    {
        return [
            CreateAuthTables::class,
            CreateApiTables::class,
        ];
    }

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
        $fiscalizations->varchar('eor', 255);
        $fiscalizations->varchar('zoi', 255);
        $fiscalizations->integer('next_id');
        $fiscalizations->datetime('requested_at');

        $companies = $this->table('companies');
        $companies->varchar('long_name');
        $companies->varchar('short_name');
        $companies->varchar('vat_number');
        $companies->varchar('business_number');
        $companies->varchar('address_line1');
        $companies->varchar('address_line2');
        $companies->varchar('address_line3');
        $companies->varchar('email');
        $companies->varchar('website');
        $companies->datetime('incorporated_at');
        $companies->integer('country_id');
        $companies->varchar('representative');

        $countries = $this->table('countries');
        $countries->boolean('default');
        $countries->varchar('phone');
        $countries->text('fiscalization_footer');
        $countries->varchar('code', 8);

        $this->save();
    }

}
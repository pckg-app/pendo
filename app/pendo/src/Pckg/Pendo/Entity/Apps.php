<?php namespace Pckg\Pendo\Entity;

use Pckg\Auth\Entity\Users;
use Pckg\Database\Entity;
use Pckg\Pendo\Record\App;

class Apps extends Entity
{

    protected $record = App::class;

    public function company()
    {
        return $this->belongsTo(Companies::class)
                    ->foreignKey('company_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class)
                    ->foreignKey('user_id');
    }

}
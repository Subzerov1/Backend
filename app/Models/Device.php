<?php

namespace App\Models;

use Illuminate\Container\Attributes\Log;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = "devices";
    protected $guarded = [];

    public function logs(){
        $this->hasMany(Log::class , "device");
    }
}

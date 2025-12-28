<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class log extends Model
{
    protected $guarded = [];
    protected $table = "devices_logs";

    public function device() {
        $this->belongsTo(Device::class, "device");
    }
}

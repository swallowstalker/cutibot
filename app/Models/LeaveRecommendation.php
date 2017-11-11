<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRecommendation extends Model
{
    protected $table = "leave_recommendations";

    protected $guarded = ["id", "created_at", "updated_at"];

    protected $appends = ["leave_date_formatted"];

    protected $casts = [
        "leave_date" => "date"
    ];

    public function getLeaveDateFormattedAttribute() {
        return $this->leave_date->formatLocalized("%A %e %b");
    }
}
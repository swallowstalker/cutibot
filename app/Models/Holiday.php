<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Holiday extends Model
{
    protected $table = "holidays";

    protected $guarded = ["id", "created_at", "updated_at"];
    protected $casts = [
        "start" => "date",
        "end" => "date",
        "recommendation_start" => "date",
        "recommendation_end" => "date"
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function scopeThisYear($query) {
        return $query->where(DB::raw("YEAR(start)"), date("Y"))
            ->where("start", ">=", DB::raw("NOW()"));
    }

    public function recommendations() {
        return $this->hasMany(LeaveRecommendation::class, "holiday_id");
    }

    public function scopeIncoming($query) {
        return $query->where("end", ">", DB::raw("NOW()"))
            ->where("end", "<=", DB::raw("DATE_ADD(NOW(), INTERVAL 6 MONTH)"));
    }

    public function scopeGiveEffect($query) {
        return $query->where("ignored", false);
    }
}

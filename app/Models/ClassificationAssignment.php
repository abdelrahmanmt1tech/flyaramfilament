<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassificationAssignment extends Model
{

    protected $fillable = ['classification_id','classifiable_id','classifiable_type'];

    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    public function classifiable()
    {
        return $this->morphTo();
    }
}

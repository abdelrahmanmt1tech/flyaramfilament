<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class LeadSource extends Model
{
    use SoftDeletes;
    use HasTranslations;
    public array $translatable = ['name'];
    protected $fillable = [
        'name',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Branch extends Model
{
    use SoftDeletes;
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'tax_number',
    ];

    public function classifications()
    {
        return $this->morphToMany(Classification::class, 'classifiable');
    }

}

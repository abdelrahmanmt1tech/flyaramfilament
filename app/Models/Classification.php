<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Classification extends Model
{
    use SoftDeletes;
    use HasTranslations;
    public array $translatable = ['name'];
    protected $fillable = [
        'name',
        'type',
    ];
    public function assignments()
    {
        return $this->hasMany(ClassificationAssignment::class);
    }
}

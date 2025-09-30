<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Airline extends Model
{
    use SoftDeletes;


    use HasTranslations;
    public array $translatable = ['name'];


    protected $fillable = [
        'name',
        'iata_code',     // كان code_string
        'iata_prefix',   // كان code
        'icao_code',
        'is_internal',
    ];

    // Normalizers
    public function setIataCodeAttribute($v){ $this->attributes['iata_code'] = $v ? strtoupper($v) : null; }
    public function setIcaoCodeAttribute($v){ $this->attributes['icao_code'] = $v ? strtoupper($v) : null; }
    public function setIataPrefixAttribute($v){
        if ($v === null || $v === '') { $this->attributes['iata_prefix'] = null; return; }
        $digits = preg_replace('/\D/','', (string)$v);
        $this->attributes['iata_prefix'] = str_pad($digits, 3, '0', STR_PAD_LEFT); // 3 خانات
    }

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }
}

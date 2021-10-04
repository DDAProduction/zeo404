<?php
namespace DDAProduction\Zeo404\Models;

use Illuminate\Database\Eloquent;


/**
 * @property string $url
 * @property string $url_md5
 * @property int $country_az
 * @property int $exclude
 *
 * @mixin \Eloquent
 */
class ZeoUrl extends Eloquent\Model
{

    protected $fillable = [
        'url',
        'url_md5',
        'count_error',
        'exclude',
    ];

    protected $attributes = [
        'count_error' => 0,
        'exclude' => 0,
    ];
}


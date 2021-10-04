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
class CheckTaskPageLink extends Eloquent\Model
{

    protected $fillable = [
        'url',
        'page_id',
        'type',
        'code',
        'info',
    ];


}


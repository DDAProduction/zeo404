<?php
namespace DDAProduction\Zeo404\Models;

use Illuminate\Database\Eloquent;


/**
 * @property string $zeo_url_id
 * @property string $referer
 * @property string $user_data
 *
 * @mixin \Eloquent
 */
class ZeoUrlFail extends Eloquent\Model
{

    protected $fillable = [
        'zeo_url_id',
        'referer',
        'user_data',
    ];

}


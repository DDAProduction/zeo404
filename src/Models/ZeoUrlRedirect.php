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
class ZeoUrlRedirect extends Eloquent\Model
{

    protected $fillable = [
        'zeo_url_id',
        'page_id',
        'exclude',
    ];

    protected $attributes = [
        'exclude' => 0,
    ];
}


<?php
namespace DDAProduction\Zeo404\Models;

use Illuminate\Database\Eloquent;


/**
 * @property string $count_link
 * @property string $task_id
 * @property int $country_az
 * @property int $exclude
 *
 * @mixin \Eloquent
 */
class CheckTaskPage extends Eloquent\Model
{

    protected $fillable = [
        'url',
        'task_id',
        'status',
        'count_link',
        'count_js_links',
        'count_phone_links',
        'count_empty_links',
        'count_error_link',
        'count_image',
        'count_error_image',
        'count_blank',
        'count_empty_image',
    ];


}


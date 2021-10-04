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
class CheckTask extends Eloquent\Model
{

    protected $fillable = [
        'name',
        'date_end',
        'count_page',
        'count_link',
        'count_js_links',
        'count_phone_links',
        'count_empty_links',
        'count_image',
        'count_error_link',
        'count_error_image',
        'count_blank',
        'count_empty_image',
    ];


}


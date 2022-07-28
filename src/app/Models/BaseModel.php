<?php

namespace Ipsum\Core\app\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ipsum\Core\app\Models\BaseModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends Model
{

    protected $perPage = 25;

    protected $casts = [
        'options' => 'array',
    ];
}

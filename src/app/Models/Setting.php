<?php

namespace Ipsum\Core\app\Models;


/**
 * Ipsum\Core\app\Models\Setting
 *
 * @property int $id
 * @property string $group
 * @property string $key
 * @property string $name
 * @property string|null $description
 * @property string|null $value
 * @property string $type
 * @property array|null $options
 * @property string|null $rules
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @mixin \Eloquent
 */
class Setting extends BaseModel
{
    protected $table = 'settings';

    protected $casts = [
        'options' => 'array',
    ];
}

<?php

namespace Ipsum\Core\app\Models;


/**
 * Ipsum\Core\app\Models\Translate
 *
 * @property int $id
 * @property string $translatable_type
 * @property int $translatable_id
 * @property string $locale
 * @property string $attribut
 * @property string $value
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $translatable
 * @method static \Illuminate\Database\Eloquent\Builder|Translate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Translate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Translate query()
 * @mixin \Eloquent
 */
class Translate extends BaseModel
{

    protected $fillable = ['locale', 'attribut', 'value'];


    public $timestamps = false;


    /*
     * Relations
     */


    public function translatable()
    {
        return $this->morphTo();
    }

}

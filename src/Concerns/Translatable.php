<?php

namespace Ipsum\Core\Concerns;



use Illuminate\Database\Eloquent\Builder;
use Ipsum\Core\app\Models\Translate;

trait Translatable
{


    /**
     * Traduction chargée
     *
     * @var boolean
     */
    protected $translate_loaded = false;



    /**
     * Attributs à traduire
     *
     * @var array
     */
    // protected $translatable_attributes = [];


    /**
     * Attributs complémentaires à traduire (custom_fields par exemple)
     * Si string -> config()
     *
     * @var array|string
     */
    //protected $translatable_attributes_adds = ['custom_fields.text'];



    protected static function bootTranslatable()
    {

        if (!self::isDefaultCurrentLocale()) {

            // Eager Loading de la traduction avec la langue
            static::addGlobalScope('translatesScope', function (Builder $builder) {
                $builder->with(['translates' => function ($builder) {
                    $builder->where('locale', self::currentLocale());
                }]);
            });


            // Enregistrement dans la table translate
            static::saving(function (self $objet) {
                foreach ($objet->translatableAttributes() as $attribute) {

                    if ($objet->isCustomFieldAttribute($attribute)) {
                        $infos = explode('.', $attribute);

                        $data = !empty($objet->attributes[$infos[0]]) ? json_decode($objet->attributes[$infos[0]], true) : null;

                        $objet->attributes[$infos[0]] = json_encode($data);

                        /*dd($objet->{$infos[0]}, $infos[1], $objet->{$infos[0]}->{$infos[1]}, $objet->getAttributes());
                        dd($objet->getAttributes());
                        $value = $objet->{$infos[0]}->{$infos[1]};*/
                    } else {
                        $value = $objet->$attribute;
                    }

                    if ($value !== null) {
                        $objet->translates()->updateOrCreate([
                            'locale' => self::currentLocale(),
                            'attribut' => $attribute,
                        ], [
                            'value' => $value,
                        ]);
                    }

                    // Annulation des champs translatable de base
                    // TODO ne fonctionne pas avec les custom fields
                    if (!$objet->isCustomFieldAttribute($attribute)) {

                        $objet->setAttribute($attribute, $objet->getOriginal($attribute));
                    } else {
                        // TODO refactoriser
                        $objet->setAttribute('custom_fields', $objet->getOriginal('custom_fields'));
                    }
                }
            });

        }
    }





    public function translates()
    {
        return $this->morphMany(Translate::class, 'translatable');
    }




    public function getAttributeValue($key)
    {
        // Récupèration de la traduction
        // J'aurais préfèré utiliser Model::setRawAttributes() ou l'événement retrieved mais cela ne fonctionne pas, car le Eager Loading ne semble pas en place à ce moment

        if ($key !== 'id' and !self::isDefaultCurrentLocale() and !$this->translate_loaded and $this->relationLoaded('translates') and $this->isTranslatableAttribute($key)) {
            // Indique de ne pas recharger la traduction. A mettre au debut à cause des boucles infini
            $this->translate_loaded = true;

            foreach ($this->translates as $translate) {
                if ($this->isCustomFieldAttribute($translate->attribut)) {
                    /*$infos = explode('.', $translate->attribut);
                    //dump($translate->attribut, $translate->value, $this->attributes[$infos[0]], $this->{$infos[0]}->{$infos[1]});
                   $custom_field = $this->{$infos[0]};
                   $custom_field->{$infos[1]} = $translate->value;
                   $this->{$infos[0]} = $custom_field;
                    dd($this->attributes);*/
                } else {
                }
                $this->attributes[$translate->attribut] = $translate->value;
            }

            //dd($this->attributes);

          }

        return parent::getAttributeValue($key);
    }





    static function currentLocale() :string
    {
        // On prend request()->route('locale') dans le cas de l'admin
        return request()->route('locale') ?? app()->currentLocale();
    }

    static function isDefaultCurrentLocale() :bool
    {
        return self::currentLocale() == config('ipsum.translate.default_locale');
    }

    protected function translatableAttributes()
    {
        $attributs = $this->translatable_attributes;
        if (property_exists($this, 'translatable_attributes_adds')) {
            $attributs = array_merge($attributs, is_array($this->translatable_attributes_adds) ? $this->translatable_attributes_adds : config($this->translatable_attributes_adds));
        }

        return $attributs;
    }

    protected function isTranslatableAttribute($key)
    {
        return in_array($key, $this->translatableAttributes());
    }

    protected function isCustomFieldAttribute($key)
    {
        // if ($objet->$attribute instanceof CustomFields)
        return isset(explode('.', $key)[1]);
    }


}

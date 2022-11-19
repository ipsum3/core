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

                $translate_deleted = [];

                foreach ($objet->translatableAttributes() as $attribute) {

                    $value = null;
                    if ($objet->isCustomFieldAttribute($attribute)) {
                        $infos = explode('.', $attribute);
                        $custom_fields_name = $infos[0];
                        $custom_fields_attribut = $infos[1];


                        if (isset($objet->attributes[$custom_fields_name]) or $objet->attributes[$custom_fields_name] != '') {
                            // récupèaration de la valeur pour enregitrement
                            $custom_fields = json_decode($objet->attributes[$custom_fields_name], true);
                            $value = $custom_fields[$custom_fields_attribut] ?? null;
                        }

                        if (isset($objet->original[$custom_fields_name]) or $objet->original[$custom_fields_name] != '') {
                            // Annulation des champs translatable de base
                            $custom_fields_original = json_decode($objet->original[$custom_fields_name], true);
                            $custom_fields[$custom_fields_attribut] = $custom_fields_original[$custom_fields_attribut] ?? null;
                            $objet->attributes[$custom_fields_name] = json_encode($custom_fields);
                        }

                    } else {
                        $value = $objet->attributes[$attribute];

                        // Annulation des champs translatable de base
                        $objet->attributes[$attribute] = $objet->original[$attribute];
                    }

                    if ($value !== null) {
                        $objet->translates()->updateOrCreate([
                            'locale' => self::currentLocale(),
                            'attribut' => $attribute,
                        ], [
                            'value' => $value,
                        ]);
                    } else {
                        $translate_deleted[] = $attribute;
                    }
                }

                $objet->translates()->whereIn('attribut', $translate_deleted)->delete();
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
        // J'aurais préfèré utiliser Model::setRawAttributes() ou l'événement retrieved mais cela ne fonctionne pas,
        // car le Eager Loading ne semble pas en place à ce moment

        if ($key !== 'id' and !self::isDefaultCurrentLocale() and !$this->translate_loaded and $this->relationLoaded('translates') and $this->isTranslatableAttribute($key)) {
            // Indique de ne pas recharger la traduction. A mettre au debut, pour éviter les boucles infini
            // dans le cas ou getter est utiliser dans cette méthode
            $this->translate_loaded = true;

            foreach ($this->translates as $translate) {
                if ($this->isCustomFieldAttribute($translate->attribut)) {
                    $infos = explode('.', $translate->attribut);
                    $custom_fields_name = $infos[0];
                    $custom_fields_attribut = $infos[1];

                    // récupèaration de la valeur pour enregitrement
                    $custom_fields = json_decode($this->attributes[$custom_fields_name], true);
                    $custom_fields[$custom_fields_attribut] = $translate->value;
                    $this->attributes[$custom_fields_name] = json_encode($custom_fields);
                } else {
                    $this->attributes[$translate->attribut] = $translate->value;
                }
            }
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
        if (property_exists($this, 'translatable_attributes_adds') and config()->has($this->translatable_attributes_adds)) {
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

<?php

namespace Ipsum\Core\Concerns;

use Str;

trait Slug
{
    protected $slugChamp = 'slug';

    protected static function bootSlug()
    {

        static::creating(function ($objet) {

            if (!property_exists($objet, 'slugBase')){
                throw new \Exception("Pas de slugBase");
            }

            if ($objet->slug === null) {
                $base = $objet->slugBase;
                $objet->slug = $objet->$base;
            }
        });

    }

    protected function setSlugAttribute($slug)
    {
        if (empty($slug)) {
            return;
        }

        $base = $this->slugBase;

        $slug = Str::slug($slug);

        // Renomme si slug existe déja
        $count = 1;
        while ( static::where($this->slugChamp, $slug)->where('id', '!=', !$this->exists ? 0 : $this->id)->count()) { //->withTrashed()
            $slug = Str::slug($this->$base).'('.$count++.')';
        }

        $this->attributes[$this->slugChamp] = $slug;
    }
    
    public function getRouteKeyName()
    {
        return $this->slugChamp;
    }
}

<?php

/*
 * Class for generalized media helper functions.
 * @author Arslan Arif <AArif.KFZ@emaar.ae>
 */

namespace App\Helpers;

class SlugHelper
{

    private $string;
    private $slug;
    private $model;
    private $separator;

    public function __construct($model, $string, $separator = '-')
    {
        $this->string    = $string;
        $this->model     = $model;
        $this->separator = $separator;
    }

    public function getSlug()
    {
        return $this->generate();
    }

    private function generate()
    {
        $slug = str_slug($this->string);

        $allSlugs = $this->similarSlugs($slug);

        if (!$allSlugs->contains('slug', $slug)) {
            return $slug;
        }

        for ($i = 1; $i <= 20; $i++) {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                return $newSlug;
            }
        }

        throw new \Exception('Unable to create a new unique slug');
    }

    protected function similarSlugs($slug)
    {
        $model = $this->model;

        $sql = $model->select('slug')->where('slug', 'like', '%' . $slug . '%')
            ->where('id', '<>', $model->id);

        if($model::SOFT_DELETES) {
            $sql = $sql->withTrashed();
        }

        $collection = $sql->get();

        return $collection;
    }

}

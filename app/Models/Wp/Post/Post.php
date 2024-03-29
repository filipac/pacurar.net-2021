<?php
namespace App\Models\Wp\Post;

use LaraWelP\Foundation\Support\Wp\Model\Taxonomy;
use LaraWelP\Foundation\Support\Wp\Model\Post as BaseModel;

class Post extends BaseModel
{
    public function __construct($pid = null)
    {
        parent::__construct($pid);

        //
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function tags()
    {
        $tags = (new Taxonomy('post_tag'))->theTerms($this);

        return $tags;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function categories()
    {
        $categories = (new Taxonomy('category'))->theTerms($this);

        return $categories;
    }

    /**
     * Change the date output format of parent's
     *
     * @param string $format
     *
     * @return mixed
     */
    public function date($format = 'F jS, Y')
    {
        //December 24th, 2015
        return parent::date($format);
    }

    public function gradient_colors(): string
    {
        $tagNames = $this->tags()->map(fn ($item) => $item->name());
        $categories = $this->categories()->map(fn ($item) => $item->slug());
        // $tagNames = ;
        if ($categories->contains('family-life')) {
            return 'from-purple-900 to-pink-400';
        } elseif ($tagNames->contains('laravel')) {
            return 'from-red-400 to-red-700';
        }

        if ($tagNames->contains('php')) {
            return 'from-blue-500 to-blue-800';
        }

        if ($tagNames->contains('javascript')) {
            return 'from-yellow to-orange-500';
        }

        return 'from-yellow to-green-400';
    }

    public function ogImageBaseUrl()
    {
        $info = wpml_get_language_information(null, $this->id());
        if (is_array($info) && isset($info['language_code']) && $info['language_code'] == 'en') {
            return 'https://pacurar.dev/ogImage/'.$this->id();
        }
        return 'https://pacurar.net/ogImage/'.$this->id();
    }
}

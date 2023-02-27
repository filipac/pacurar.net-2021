<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSpace extends Model
{
    protected $fillable = ['name', 'content', 'last_modified_by'];

    public static function getAdSpace($name)
    {
        return self::query()->where('name', $name)->firstOrNew();
    }

    public function sanitizedHtml()
    {
        if(!$this->content) {
            return '';
        }
        $dom = new \DOMDocument();
        $dom->loadHTML($this->content ?? '', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);
        // get all script tags and remove them
        $scripts = $dom->getElementsByTagName('script');
        $remove = [];
        foreach ($scripts as $item) {
            $remove[] = $item;
        }
        foreach ($remove as $item) {
            $item->parentNode->removeChild($item);
        }

        return $dom->saveHTML();
    }
}

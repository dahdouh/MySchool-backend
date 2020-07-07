<?php


namespace App\Twig;

use Traversable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('shuffle', [$this, 'shuffle']),
        ];
    }

    public function shuffle($array)
    {
        if ($array instanceof Traversable) {
            $array = iterator_to_array($array, false);
        }

        shuffle($array);

        return $array;
    }
}
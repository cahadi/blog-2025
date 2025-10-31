<?php

namespace App\Traits;

trait Helper
{
    public static function dd($something)
    {
        echo '<pre>';
        print_r($something);
        echo '</pre>';
        exit();
    }
}
<?php
namespace App;

function renderPhp($file, $data = []): string
{
    ob_start();
    extract($data);
    require $file;
    return ob_get_clean();
}

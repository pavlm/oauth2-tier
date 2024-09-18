<?php
namespace App;

use Amp\Http\Server\Response;

function renderPhp($file, $data = []): string
{
    ob_start();
    extract($data);
    require $file;
    return ob_get_clean();
}

/**
 * Gets nested property with json pointer 
 */
function getViaPointer($data, array|string $jsonPointer, mixed $default = null): mixed
{
    $parts = is_array($jsonPointer) ? $jsonPointer : explode('/', substr($jsonPointer, 1));
    $prop = array_shift($parts);
    if ($prop === null) {
        throw new \Exception('wrong json pointer.');
    }
    if (!(is_string($prop) || is_integer($prop))) {
        throw new \Exception('wrong json pointer. token: ' . $prop);
    }
    $value = $data[$prop] ?? $default;
    return empty($parts) ? $value : getViaPointer($value, $parts, $default);
}

function responseWithRedirect(string $location, int $status = 302)
{
    return new Response($status, [
        'location' => $location,
    ]);
}

<?php
namespace App\Symfony;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['container.env_var_processor'])]
class EnvVarGraphProcessor implements EnvVarProcessorInterface
{

    public function __construct()
    {
    }
    
    public static function getProvidedTypes(): array
    {
        return [
            'graph' => 'array',
        ];
    }

    public function getEnv(string $prefix, string $name, \Closure $getEnv): mixed
    {
        $it = new \RegexIterator(new \ArrayIterator($_ENV), sprintf('#^%s_.+#', $name), 0, \RegexIterator::USE_KEY);
        $list = [...$it];
        $keys = array_map(fn ($key) => explode($name . '_', $key)[1], array_keys($list)); // without $name prefix
        $list = array_combine($keys, array_values($list));
        
        // Prop path with indexes: 'A_1_B' -> ['A', 1, 'B']
        $propPath = function ($propName) {
            $propName = trim($propName, '_');
            $words = new \ArrayIterator(explode('_', $propName));
            
            while ($words->valid() && ($word = $words->current()) !== null) {
                if (is_numeric($word)) {
                    yield $word;
                } else {
                    $nonIndex = [$word];
                    $words->next();
                    while ($words->valid() && ($word = $words->current()) !== null) {
                        if (is_numeric($words->current())) {
                            yield implode('_', $nonIndex);
                            continue 2;
                        }
                        $nonIndex[] = $word;
                        $words->next();
                    }
                    yield implode('_', $nonIndex);
                }
                $words->valid() && $words->next();
            }
        };
        
        $setProp = function (&$array, array $propPath, $value) use (&$setProp) {
            $prop = array_shift($propPath);
            $array[$prop] = empty($propPath) ? $value : ($array[$prop] ?? []);
            if (!empty($propPath)) {
                $setProp($array[$prop], $propPath, $value);
            }
        };
        
        $graph = [];
        foreach ($list as $prop => $v) {
            $path = [...$propPath($prop)];
            $setProp($graph, $path, $v);
        }
        
        return $graph;
    }

    
}
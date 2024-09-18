<?php
namespace App\Handlers;

use Psr\Http\Message\UriInterface;

trait RequestHandlerTrait
{
    
    public function getLocationPathPrefix(): string
    {
        $pathPrefix = $this->config->getUrlPathPrefix();
        return rtrim($pathPrefix . $this->locationConfig->location, '/');
    }
    
    /**
     * Url path without location prefix
     * @param UriInterface $uri
     * @param boolean $validate
     * @return string|null
     */
    public function getInternalPath(UriInterface $uri, $validate = true): ?string
    {
        $path = $uri->getPath();
        $pathPrefix = $this->config->getUrlPathPrefix();
        $pathPrefix = rtrim($pathPrefix . $this->locationConfig->location, '/');
        if ($validate && !str_starts_with($path, $pathPrefix)) {
            return null;
        }
        $path = substr($path, strlen($pathPrefix));
        return $this->filterUrlPath($path);
    }
    
    public function filterUrlPath($path)
    {
        $path = implode('/', array_filter(explode('/', $path), fn ($seg) => $seg !== '..'));
        $path = preg_replace('#//+#', '/', $path);
        return $path;
    }
    
}
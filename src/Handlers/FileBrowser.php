<?php
namespace App\Handlers;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(autowire: false)]
class FileBrowser
{
    const MAX_URL_LENGTH = 2048;
    
    public $targetDir;
    public $targetFile;
    private $directFileLink = false;
    
    public $dirContent = [];
    public $fileContent;
    
    public $dirError = null;
    public $fileError = null;
    
    public function __construct(
        public string $rootDir,
        public string $virtualExtension = 'html',
        public int $maxFileSize = 1024 * 1024,
    )
    {
        $this->rootDir = realpath2($rootDir);
    }
    
    /**
     * @param string $url Relative file or dir url
     */
    public function selectTarget(string $url)
    {
        $url1 = new FileUrl($url, $this->rootDir);    
        
        if ($url1->isDir()) {
            $this->targetDir = $url1;
            return;
        }
        
        if ($url1->isFile()) {
            $this->targetFile = $url1;
            $this->targetDir = $url1->getParent();
            return;
        }
        
        $extension = $url1->getExtension();
        if ($extension == $this->virtualExtension) {
            $url2 = new FileUrl(substr($url1->url, 0, -strlen($extension)-1), $this->rootDir); // url without extension
            
            if ($url2->isDir()) {
                $this->targetDir = $url2;
                return;
            }
            
            if ($url2->isFile()) {
                $this->targetFile = $url2;
                $this->targetDir = $url2->getParent();
                return;
            }
        } else { // no extension
            $url2 = $url1->getParent();
            if ($url2->isDir()) {
                $this->targetDir = $url2;
                $this->targetFile = $url1;
                $this->fileError = new \Exception('file not found');
                return;
            }
        }

        $this->dirError = new \Exception('directory not found');
        $this->fileError = new \Exception('file not found');
    }
    
    public function readTarget()
    {
        if ($this->targetDir) {
            $it = new \LimitIterator(new \DirectoryIterator($this->targetDir->path), 0, 1000);
            $this->dirContent = [];
            foreach ($it as $file) {
                if ($file->isDot()) continue;
                $this->dirContent[] = clone $file->getFileInfo();
            }
            usort($this->dirContent, fn ($f1, $f2) => $f2->isDir() <=> $f1->isDir() ?: $f1->getFilename() <=> $f2->getFilename());
        }
        
        $this->fileContent = null;
        if ($this->targetFile && !$this->fileError) {
            $size = filesize($this->targetFile->path);
            if ($size > $this->maxFileSize) {
                $this->fileError = new \Exception('too large file');
                return;
            }
            $this->fileContent = @file_get_contents($this->targetFile->path);
            if (false === $this->fileContent) {
                $this->fileError = new \Exception('file read error');
                return;
            }
        }
    }
    
}

class FileUrl
{
    public $fileRoot;
    public $url;
    private bool $rootUrl;
    public $path;
    private $parts;
    
    /**
     * @param string $url relative url
     * @param string $fileRoot fs directory
     */
    public function __construct(string $url, string $fileRoot)
    {
        $this->fileRoot = $fileRoot;
        $this->url = $this->filterPath($url);
        $this->rootUrl = strlen($this->url) == 1;
        $this->path = $this->fileRoot . $this->url;
        $this->parts = pathinfo($this->path);
    }
    
    private function filterPath($path)
    {
        $path = implode('/', array_filter(explode('/', $path), fn ($seg) => $seg !== '..'));
        $path = preg_replace('#//+#', '/', $path);
        $path = rtrim($path, '/');
        $path = $path ?: '/';
        $path = $path[0] == '/' ? $path :  ('/' . $path);
        return $path;
    }
    
    public function isDir()
    {
        return is_dir($this->path);
    }
    
    public function isFile()
    {
        return is_file($this->path);
    }
    
    public function getBasename()
    {
        return $this->parts['basename'];
    }
    
    public function getUrlBasename()
    {
        return pathinfo($this->url, PATHINFO_BASENAME);
    }
    
    public function getDirname()
    {
        return $this->parts['dirname'];
    }
    
    public function getExtension()
    {
        return $this->parts['extension'] ?? '';
    }
    
    public function getParent(): ?self
    {
        return $this->rootUrl ? null : new FileUrl(preg_replace('#/[^/]+$#', '', $this->url), $this->fileRoot);
    }
    
    public function getSegmentUrls(): array
    {
        $segments = $this->rootUrl ? [] : array_slice(explode('/', $this->url), 1);
        $current = '';
        $urls = [];
        foreach ($segments as $seg) {
            $current .= '/' . $seg;
            $urls[] = new FileUrl($current, $this->fileRoot);
        }
        array_unshift($urls, new FileUrl('/', $this->fileRoot));
        return $urls;
    }
    
    public function __toString()
    {
        return $this->url;
    }
    
}

function realpath2($path) 
{
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = explode(DIRECTORY_SEPARATOR, $path);
    $absolutes = [];
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return implode(DIRECTORY_SEPARATOR, $absolutes);
}

function iterator_map($iterator, $fn)
{
    foreach ($iterator as $item) {
        yield $fn($item);
    }
}

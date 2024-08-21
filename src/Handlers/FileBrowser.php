<?php
namespace App\Handlers;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(autowire: false)]
class FileBrowser
{
    const MAX_URL_LENGTH = 2048;
    
    public $targetDir = '/';
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
        if (strlen($url) > self::MAX_URL_LENGTH) {
            throw new \Exception('too long url');
        }
        
        $path = realpath2($this->rootDir . $url);
        if (strlen($path) < strlen($this->rootDir)) {
            $this->dirError = new \Exception('wrong target');
            $this->targetDir = $url;
            $this->targetFile = null;
            return;
        }
        $relPath = substr($path, strlen($this->rootDir));
        ['dirname' => $relDir, 'basename' => $lastName] = pathinfo($relPath);
        $relDir .=  $relDir[-1] !== '/' ? '/' : '';
        
        if (is_dir($path)) {
            $this->targetDir = $relPath;
            $this->targetFile = null;
            return;
        }
        
        if (is_file($path)) {
            $this->directFileLink = true;
            $this->targetDir = $relDir;
            $this->targetFile = $lastName;
            return;
        }
        
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension == $this->virtualExtension) {
            $path2 = substr($path, 0, -strlen($extension)-1);
            $relPath2 = substr($path2, strlen($this->rootDir));
            $basename2 = pathinfo($path2, PATHINFO_BASENAME);
            
            if (is_dir($path2)) {
                $this->targetDir = $relPath2;
                $this->targetFile = null;
                return;
            }
            
            if (is_file($path2)) {
                $this->targetDir = $relDir;
                $this->targetFile = $basename2;
                return;
            }
            $this->dirError = new \Exception('directory not found');
            $this->fileError = new \Exception('file not found');
            $this->targetDir = $relPath;
            $this->targetFile = null;
            return;
        }
        
        // check $path parent dir
        ['dirname' => $pathDirname, 'basename' => $pathBasename] = pathinfo($path);
        $pathDirname .=  $pathDirname[-1] !== '/' ? '/' : '';
        if (is_dir($pathDirname)) {
            $relPathDirname = substr($pathDirname, strlen($this->rootDir));
            $this->targetDir = $relPathDirname;
            $this->targetFile = $pathBasename;
            $this->fileError = new \Exception('file not found');
            return;
        }
        
        $this->dirError = new \Exception('directory not found');
        $this->fileError = new \Exception('file not found');
        $this->targetDir = $relPath;
        $this->targetFile = null;
    }
    
    public function readTarget()
    {
        $dir = $this->rootDir . $this->targetDir;
        if (is_dir($dir)) {
            $this->dirContent = new \DirectoryIterator($dir);
        } else {
            $this->dirContent = [];
        }
        
        $this->fileContent = null;
        if ($this->targetFile && !$this->fileError) {
            $file = $this->rootDir . $this->targetDir . $this->targetFile;
            if (!is_file($file)) {
                $this->fileError = new \Exception('too large file');
                return;
            }
            $size = filesize($file);
            if ($size > $this->maxFileSize) {
                $this->fileError = new \Exception('too large file');
                return;
            }
            $this->fileContent = @file_get_contents($file);
            if (false === $this->fileContent) {
                $this->fileError = new \Exception('file read error');
                return;
            }
        }
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

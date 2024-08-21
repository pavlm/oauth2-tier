<?php
use PHPUnit\Framework\TestCase;
use App\Handlers\FileBrowser;

class FileBrowserTest extends TestCase
{

    /**
     * @dataProvider data_of_test 
     */
    public function test($target, $targetDir, $targetFile, $dirContent, $fileContent, $dirError, $fileError)
    {
        
        $b = new FileBrowser(__DIR__ . '/data');
        $b->selectTarget($target);
        $b->readTarget();
        
        $this->assertEquals($targetDir, $b->targetDir?->url, 'targetDir');
        $this->assertEquals($targetFile, $b->targetFile?->getBasename(), 'targetFile');
        $this->assertEquals($dirContent, $b->dirContent, 'dirContent');
        $this->assertEquals($fileContent, $b->fileContent, 'fileContent');
        if ($dirError === false) {
            $this->assertEmpty($b->dirError, 'dirError');
        } else {
            $this->assertNotEmpty($b->dirError, 'dirError');
            $this->assertStringContainsString($dirError, $b->dirError->getMessage(), 'dirError');
        }
        if ($fileError === false) {
            $this->assertEmpty($b->fileError, 'fileError');
        } else {
            $this->assertNotEmpty($b->fileError, 'fileError');
            $this->assertStringContainsString($fileError, $b->fileError->getMessage(), 'fileError');
        }
        
    }
    
    public function data_of_test()
    {
        return [
            [
                'target' => '/',
                'targetDir' => '/',
                'targetFile' => null,
                'dirContent' => ['..', 'test.txt',],
                'fileContent' => null,
                'dirError' => false,
                'fileError' => false,
            ],
            [
                'target' => '/test.txt',
                'targetDir' => '/',
                'targetFile' => 'test.txt',
                'dirContent' => ['..', 'test.txt',],
                'fileContent' => 'contents of test.txt',
                'dirError' => false,
                'fileError' => false,
            ],
            [
                'target' => '/unreal',
                'targetDir' => '/',
                'targetFile' => 'unreal',
                'dirContent' => ['..', 'test.txt',],
                'fileContent' => null,
                'dirError' => false,
                'fileError' => 'not found',
            ],
        ];
    }
    
}
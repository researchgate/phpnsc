<?php
namespace rg\tools\phpnsc;

use PHPUnit\Framework\TestCase;

class DirectoryScannerTest extends TestCase
{

    /**
     * @var DirectoryScanner
     */
    private $directoryScanner;

    protected function setUp(): void {
        parent::setUp();
        $filesystem = new DirectoryScannerFilesystemMock('/root/folder');
        $root = '/root/folder';
        $this->directoryScanner = new DirectoryScanner($filesystem, $root, '/');
    }

    public function testReadDirectory() {
        $this->directoryScanner->includeDirectory('one');
        $this->directoryScanner->includeDirectory('two');
        $this->directoryScanner->excludeDirectory('two/subfolder2');
        $this->directoryScanner->includeFiletype('.php');
        $this->directoryScanner->excludeFiletype('.tpl.php');

        $files = $this->directoryScanner->getFiles();

        $expected = array(
            '/root/folder/one/subfolder1/included11.php',
            '/root/folder/one/subfolder1/included11.two.php',
            '/root/folder/one/included1.php',
            '/root/folder/one/included1.two.php',
            '/root/folder/two/included2.php',
            '/root/folder/two/included2.two.php',
        );

        $this->assertEquals($expected, $files);
    }
}

class DirectoryScannerFilesystemMock extends FilesystemAccess
{
    private $filesystem = array(
        '/root/folder/one' => array(
            '.',
            '..',
            'subfolder1',
            'testfile1',
            'included1.php',
            'included1.two.php',
            'excluded1.tpl.php',

        ),
        '/root/folder/one/subfolder1' => array(
            '.',
            '..',
            'testfile11',
            'included11.php',
            'included11.two.php',
            'excluded11.tpl.php',

        ),
        '/root/folder/two' => array(
            '.',
            '..',
            'subfolder2',
            'testfile2',
            'included2.php',
            'included2.two.php',
            'excluded2.tpl.php',
        ),
        '/root/folder/two/subfolder2' => array(
            '.',
            '..',
            'testfile22',
            'included22.php',
            'included22.two.php',
            'excluded22.tpl.php',
        ),
    );

    private $currentDirectory = array();
    private $currentItem = array();

    public function openDirectory($directory) {
        $directory = str_replace('\\', '/', $directory); // Normalize path on Windows
        $handle = count($this->currentDirectory) + 1;
        $this->currentDirectory[$handle] = $directory;
        $this->currentItem[$handle] = 0;
        return isset($this->filesystem[$directory]) ? $handle : false;
    }

    public function closeDirectory($handle): bool {
        return true;
    }

    public function isDir($path): bool {
        return isset($this->filesystem[$path]);
    }

    public function realpath($path) {
        return $path;
    }

    public function readdir($handle) {
        if (! isset($this->filesystem[$this->currentDirectory[$handle]][$this->currentItem[$handle]])) {
            return false;
        }
        $this->currentItem[$handle]++;
        return $this->filesystem[$this->currentDirectory[$handle]][$this->currentItem[$handle] - 1];
    }
}

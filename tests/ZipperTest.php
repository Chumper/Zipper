<?php

use Chumper\Zipper\Zipper;
use Illuminate\Filesystem\Filesystem;

require_once 'ArrayArchive.php';

class ZipperTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var \Chumper\Zipper\Zipper
     */
    public $archive;

    /**
     * @var \Mockery\Mock
     */
    public $file;

    public function __construct()
    {
        $this->archive = new \Chumper\Zipper\Zipper(
            $this->file = Mockery::mock(new Filesystem)
        );
        $this->archive->make('foo', new ArrayArchive('foo'));
    }

    public function testMake()
    {
        $this->assertEquals('ArrayArchive', $this->archive->getArchiveType());
        $this->assertEquals('foo', $this->archive->getFilePath());
    }

    public function testExtractTo()
    {

    }

    public function testAddAndGet()
    {
        $this->file->shouldReceive('isFile')->with('foo.bar')
            ->times(3)->andReturn(true);
        $this->file->shouldReceive('isFile')->with('foo')
            ->times(3)->andReturn(true);

        /**Array**/
        $file1 = Mockery::mock('File');
        $file1->shouldReceive('getRelativePathname')
            ->andReturn('foo.bar');
        $file2 = Mockery::mock('File');
        $file2->shouldReceive('getRelativePathname')
            ->andReturn('foo');

        $this->file->shouldReceive('isFile')->with('fooDir')
            ->once()->andReturn(false);
        $this->file->shouldReceive('allFiles')->with('fooDir')
            ->once()->andReturn(array($file1, $file2));

        //test1
        $this->archive->add('foo.bar');
        $this->archive->add('foo');

        $this->assertEquals('foo', $this->archive->getFileContent('foo'));
        $this->assertEquals('foo.bar', $this->archive->getFileContent('foo.bar'));

        //test2
        $this->archive->add(array(
            'foo.bar',
            'foo'
        ));
        $this->assertEquals('foo', $this->archive->getFileContent('foo'));
        $this->assertEquals('foo.bar', $this->archive->getFileContent('foo.bar'));

        //test3
        $this->archive->add('fooDir');

        $this->assertEquals('foo', $this->archive->getFileContent('foo'));
        $this->assertEquals('foo.bar', $this->archive->getFileContent('foo.bar'));

    }

    /**
     * @expectedException Exception
     */
    public function testGetFileContent()
    {
        $this->archive->getFileContent('baz');
    }

    public function testRemove()
    {
        $this->file->shouldReceive('isFile')->with('foo')
            ->andReturn(true);

        $this->archive->add('foo');

        $this->assertTrue($this->archive->contains('foo'));

        $this->archive->remove('foo');

        $this->assertFalse($this->archive->contains('foo'));

        //----

        $this->file->shouldReceive('isFile')->with('foo')
            ->andReturn(true);
        $this->file->shouldReceive('isFile')->with('fooBar')
            ->andReturn(true);

        $this->archive->add(array('foo', 'fooBar'));

        $this->assertTrue($this->archive->contains('foo'));
        $this->assertTrue($this->archive->contains('fooBar'));

        $this->archive->remove(array('foo', 'fooBar'));

        $this->assertFalse($this->archive->contains('foo'));
        $this->assertFalse($this->archive->contains('fooBar'));
    }

    public function testExtractWhiteList()
    {
        $this->file->shouldReceive('isFile')->with('foo')
            ->andReturn(true);

        $this->archive->add('foo');

        $this->file->shouldReceive('put')->with(realpath(NULL) . '/foo', 'foo');

        $this->archive->extractTo('', array('foo'), Zipper::WHITELIST);

        //----
        $this->file->shouldReceive('isFile')->with('foo')
            ->andReturn(true);

        $this->archive->folder('foo/bar')->add('foo');

        $this->file->shouldReceive('put')->with(realpath(NULL) . '/foo', 'foo/bar/foo');

        $this->archive->extractTo('', array('foo'), Zipper::WHITELIST);

    }

    public function testExtractBlackList()
    {

    }

    public function testNavigationFolderAndHome()
    {
        $this->archive->folder('foo/bar');
        $this->assertEquals('foo/bar', $this->archive->getCurrentFolderPath());

        //----

        $this->file->shouldReceive('isFile')->with('foo')
            ->andReturn(true);

        $this->archive->add('foo');
        $this->assertEquals('foo/bar/foo', $this->archive->getFileContent('foo/bar/foo'));

        //----

        $this->file->shouldReceive('isFile')->with('bar')
            ->andReturn(true);

        $this->archive->home()->add('bar');
        $this->assertEquals('bar', $this->archive->getFileContent('bar'));

        //----

        $this->file->shouldReceive('isFile')->with('baz/bar/bing')
            ->andReturn(true);

        $this->archive->folder('test')->add('baz/bar/bing');
        $this->assertEquals('test/bing', $this->archive->getFileContent('test/bing'));

    }
}

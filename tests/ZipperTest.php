<?php

class ZipperTest extends PHPUnit_Framework_TestCase {

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->fs = Mockery::mock(new \Illuminate\Filesystem\Filesystem());
        $this->zip = Mockery::mock(new ZipArchive());
        $this->zipper = new \Chumper\Zipper\Zipper($this->fs,$this->zip);

    }

    public function testMakeNewZipFile()
    {
        $this->fs->shouldReceive('exists')->twice()->andReturn(false);
        $this->fs->shouldReceive('makeDirectory')->once()->andReturn(true);

        $this->zip->shouldReceive('open')->once()->andReturn(true);

        //-------

        $this->zipper->make('test.zip');
    }

   public function testMakeExistingZipFile()
    {
        $this->fs->shouldReceive('exists')->once()->andReturn(true);

        $this->zip->shouldReceive('open')->once()->andReturn(true);

        //-------

        $this->zipper->make('test.zip');
    }

    public function testGetFileContent()
    {
        $this->zip->shouldReceive('getFromName')
            ->with('abc/foo.html')->andReturn('bar');
        $this->zip->shouldReceive('locateName')
            ->with('abc/foo.html')->andReturn(true);

        $this->zip->shouldReceive('getFromName')
            ->with('baz.html')->andReturn('bar2');
        $this->zip->shouldReceive('locateName')
            ->with('baz.html')->andReturn(true);

        //-------

        $this->assertEquals('bar',$this->zipper->getFileContent('abc/foo.html'));
        $this->assertEquals('bar2',$this->zipper->getFileContent('baz.html'));

    }

    public function testAddDir()
    {
        //$zipper = new \Chumper\Zipper\Zipper;
        //$zipper->make('test.zip')->add('composer.json','test')->close();
        //$zipper->make('test.zip')->extractTo('phpunit_test');
    }

    protected function tearDown()
    {
        //if(file_exists('test.zip'))
            //unlink('test.zip');
        Mockery::close();
    }
}

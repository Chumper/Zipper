#Zipper

This is a simple Wrapper around the ZipArchive methods with some handy functions.

##Installation

To install this package just require it in your `composer.json` with

	"Chumper/Zipper": "dev-master"

This package also includes Laravel 4 support, to activate it add

	'Chumper\Zipper\ZipperServiceProvider'

to the service providers in the `app.php`

You can then access Zipper with the `Zipper` alias.

##Simple example

	$zipper = new \Chumper\Zipper\Zipper;

    $zipper->make('test.zip')->add('composer.json','test');
    $zipper->remove('composer.lock');

    $zipper->add(
        array(
            'vendor',
            'composer.json'
        ),
        'mySuperPackage');

    $zipper->getFileContent('mySuperPackage/composer.json');
	
    $zipper->make('test.zip')->extractTo('',array('mySuperPackage/composer.json'),Zipper::WHITELIST);

##Functions

**make($pathToFile)**

create or open a zip archive; if the file does not exists it will create a new one.
It will return the Zipper instance so you can chain easily


**extractTo($path, array $files = array(), $method = Zipper::BLACKLIST)**

Extracts the content of the zip archive to the specified location.
You can specify an array or string of files that will be white listed or black listed based on the third parameter


**getFileContent($filePath)**

get the content of a file in the zip. This will return the content or false.


**add($pathToAdd, $rootDirInZip = '')**

add a string or an array of files to the zip under the root dir specified in the second parameter
You can name files or folder, all files in the folder then will be added.


**getStatus()**

get the opening status of the zip as integer


**remove($fileToRemove)**

removes a single file or an array of files from the zip.


**close()**

closes the zip and writes all changes

##Development

If you need other functions or got errors, please leave an issue on github.

#Zipper

[![Build Status](https://travis-ci.org/Chumper/Zipper.png)](https://travis-ci.org/Chumper/Zipper)

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

    $zipper->make('test.zip')->folder('test')->add('composer.json');
    $zipper->zip('test.zip')->folder('test')->add('composer.json','test');
    
    $zipper->remove('composer.lock');

    $zipper->folder('mySuperPackage')->add(
        array(
            'vendor',
            'composer.json'
        ),
    );

    $zipper->getFileContent('mySuperPackage/composer.json');
	
    $zipper->make('test.zip')->extractTo('',array('mySuperPackage/composer.json'),Zipper::WHITELIST);
    
You can easily chain most functions, functions that are not chainable are `getFileContent`, `close`, `extractTo` and `getStatus`

The main reason i wrote this little package is the `extractTo` method since it allows you to be very flexible when extracting zips.
So you can for example implement an update method which will just override the changed files.

	$zipper->make('test.zip')->extractTo('public', array('vendor'), Zipper::BLACKLIST);
	
This will extract the `test.zip` into the `public` folder except the folder `vendor` inside the zip will not be extracted.

	$zipper->make('test.zip')->extractTo('public', array('vendor'), Zipper::WHITELIST);
	
This will extract the `test.zip` into the `public` folder but **only** the folder `vendor` inside the zip will be extracted.

	$zipper->make('test.zip')->folder('test')->extractTo('foo');
	
This will go into the folder `test` in the zip file and extract the content of the folder to the folder `foo`.
This command is really nice to get just a part of the zip file.

##Functions

**make($pathToFile)**

create or open a zip archive; if the file does not exists it will create a new one.
It will return the Zipper instance so you can chain easily


**extractTo($path, array $files = array(), $method = Zipper::BLACKLIST)**

Extracts the content of the zip archive to the specified location.
You can specify an array or string of files that will be white listed or black listed based on the third parameter


**getFileContent($filePath)**

get the content of a file in the zip. This will return the content or false.


**add($pathToAdd)**

add a string or an array of files to the zip
You can name files or folder, all files in the folder then will be added.


**getStatus()**

get the opening status of the zip as integer


**remove($fileToRemove)**

removes a single file or an array of files from the zip.


**close()**

closes the zip and writes all changes

**folder($folder)**

Sets the internal pointer to this folder

**home()**

Resets the folder pointer

**zip($fileName)**

USes the ZipRepository for file handling

##Development

May it is a goot idea to add other compress functions like rar, phar or bzip2 etc...
Everything is setup for that, if you want just fork and develop further.

If you need other functions or got errors, please leave an issue on github.

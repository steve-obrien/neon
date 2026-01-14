# FireFly

- [Introduction](#introduction)
- [Definitions](#definitions)
- [Configuration](#configuration)
  - [Drives](#drives)
  - [Defining Drives](#defining-drives)
- [File Manager](#file-manager)
  - [Storing Files](#storing-files)
  - [Getting File Meta Data](#getting-file-meta-data)
  - [File Uploads](#file-uploads)
  - [Exposing Files Over Http](#exposing-files-over-http)
  - [All Functions](#all-functions)
- [Drive Manager](#file-system)
  - [The Local Drive](#the-local-drive)
  - [The Db Drive](#the-db-drive)
  - [Retrieving Files](#retrieving-files)
  - [File Metadata](#file-metadata)
  - [Storing Files](#storing-files)
  - [Automatic Streaming](#automatic-streaming)
  - [Prepending & Appending To Files](#prepending-and-appending-to-files)
  - [Copying & Moving](#copying-and-moving-files)
- [Image Manager](#image-manager)

<a name="introduction"></a>
## Introduction

Neon comes shipped with a file system built on top of the file system abstraction 
layer League\Flysystem hence the name is both a hat tip to the underlying 
Flysystem library and of course the science fiction Firefly TV show.

It's fun to think of the little firefly spaceship smuggling files under 
the radar of the Alliance (Microsoft ahem! Amazon! Ahem) 


The overall goals of Firefly is to make it easy to:

 - Expose files over http (download links, and url links for images)
 - Track usage - automatic download and access counting
 - Cache files - automatic file caching
 - File system abstraction - a common interface between file systems - s3, local file system, dropbox, FTP, etc
 - Secure file management - File uploads are usually the most vulnerable aspect
 of a web system - handling files in a secure way and providing one system that 
can be improved overtime without changing the API is a good thing. 


<a name="definitions"></a>
## Definitions
 
- **Drive** - A Firefly drive is essentially a driver plus its configuration - 
a bit like mounting drives on a system each drive represents a fileystem and
could be visualised in a similar way to a desktop explorer.

- **Driver** - A class that knows how to talk to the specific filesystem (e.g. 
s3 or local filesystem) exposing a common interface to the application.

- **Firefly** - Firefly is the name of the self contained Neon app that exposes 
the *DriveManager*, *FileManager*, *MediaManager* and *ImageManager* services and provides various controllers 
and views.

- **Drive Manager** - This service provides a standard file system api 
(implementing IFileSystem) for common file manipulation operations across
various drivers (s3, local, ftp, db). The filesystem essentially talks to the 
various driver adapters

- **File Manager** - This service is an abstraction above the file system 
and deals with tracking individual files. Where as the file system has a concept 
of directories and files, the file managers only concern is files. It generates 
universally unique ids to represent files providing an easy way to fetch files
and provide permalinks that can be used to expose the file over http. 
Essentially the File manager abstracts the details of the file system away from 
the app.  The file manager could for example decide to store files across
multiple providers - or even changing providers as an application scales or has 
increased disk useage demands. This is the main firefly component and the main point 
of interaction for an app. There should be no need to use the lower level filesystem
interfaces, unless you are doing very specific file manipulations.
The File manager should always be able to find the source file as it knows the drive name and relative path.
The drive name, remember, refers to the configured drives in the drive manager config (in your neon config) that state 
the driver (local file system, google drive, ftp, etc) and its particular configuration.
It is possible to screw up the fileManager by moving the raw files around using the driveManager.
Really all file operations have to go through the fileManager. Unless you are dealing with files you do not want tracked by the fileManager.

- **Media Manager** - The media manager essentially creates a virtual file browsing
environment. Think directories and sub directories and moving files around. That's about it.
It simply maps unique fileManager id's to a virtual structure - it can know the name of the drive the real file is stored too
but can only interact with the fileManager.

- **Image Manager** - The image manager enables image files to be processed and various filters applied
for e.g. cropping and rotation etc - it also does its best to provide a common interface on top of either GD or 
ImageMagick drivers

<a name="configuration"></a>
## Configuration

Neon comes shipped with a default Firefly configuration.
Firefly itself has a top level concept of a media and cloud drives.
The default media drive - consists of a local driver configured in a sensible way.
The local driver simply points to a local file system directory. Remember a drive is a name that represents a driver and its configuration.
You could have multiple drives called 'images', 'upload', 'archive' and each drive could be powered by the local driver and simply points to different file system paths.

<a name="drives"></a>
### Drives

Neon comes shipped with the following Drivers:

- **local** - the local filesystem
- **db** - store files in the local database
- **s3** - store files in Amazon's S3 service
- **Ftp** - Ftp file system
- **Null** - A Null implementation


<a name="defining-drives"></a>
### Defining drives

A drive is essentially a Filesystem driver plus its configuration.
You can define multiple drives that can use the same driver.
For example below we define a `local` and a `public` drive both store files in 
the main project filesystem under the storage folder - you could imagine the 
public drive has a 'uploads' or similar symlink from the web accessible directory to the `@root/storage/public` folder exposing 
these files publicly on the web. Obviously doing this is not always a good idea!

Plus the fileManager manages the accessibility of files over http. 
See <a href="#exposing-over-http">exposing over http</a>

Example configuration: 

```php
'driveManager' => [
    'class' => 'neon\firefly\services\DriveManager',

    // The default drive to use when none is specified
    'default' => 'media',

    // The default cloud drive
    'cloud' => 's3'

    // All available application drives
    // This is a bit like mounting a drive
    'drives' => [
        // store files in the local file system under
        // the project root /storage directory
        'media' => [
            'driver' => 'local',
            'root' => '@root/storage'
        ],
        // store files in the local file system under
        // the project root /storage/public directory
        'public' => [
            'driver' => 'local',
            'root' => '@root/storage/public'
        ],
        // store files in a local database
        'db' => [
            'driver' => 'db',
        ],
        // store files in Amazon s3
        'cloud' => [
            'driver' => 's3',
            'key' => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket'
        ],
        // upload files to the matrix (an example of configuring your own adapter)
        'matrix' => [
            // The driver can be a full class name path
            'driver' => '\Matrix\firefly\drivers\MatrixFileAdapterClass',
            'password' => 'The one - neo'
        ]
    ]
]
```

<p class="tip">
<strong>What's the point of the default and cloud options?</strong>
Well this means the rest of the system can ask to store files in the default drive, or the cloud drive and not burden
itself with your application specific drive configuration.  Ideally the details (the names) of your particular drives 
should not leak beyond your specific application use case.
</p>

<a name="file-manager"></a>
## File Manager

<p class="tip">
The file manager API is exposed through the Firefly app itself.
Not sure if this a great idea yet - it was Neill's idea - we must always be a 
little suspicious of Neill's "ideas".  This means calls directly to firefly like `neon()->firefly->save()`
are equivelant to `neon()->firefly->fileManager->save()`. Or more precisley the neon firefly app accessible via 
`neon()->firefly` or indeed `neon('firefly')` also implements the 
`neon\firefly\services\fileManager\interfaces\IFileManager` interface
</p>

<a name="storing-files"></a>
### Storing files

To save a file using firefly.

```php
$uuid = neon()->firefly->save('The file contents');
```

The `save` function returns a uuid for the file. 
This would then be used to store in your database to reference the file in the 
future.

The File Manager also supports php resource streams. This is very useful for saving large files that would otherwise
exceed the default memory limit. This example uses `fopen` to create a stream 

```php
$uuid = neon()->firefly->save(fopen('/path/to/large/file.zip', 'r'), 'my-large-file.zip')
```

The File Manager will use streams when it can.  For example when uploading a file via the request object

<p class="tip">
Note that the db driver only emulates streams therefore if you decide to use it you must ensure files do not exceed the 
php memory limit.
</p>


<p class="tip">
Remember that the FileManager takes care of storing the file for us.
The Current implementation will use the default drive defined and generate 
a path to store the file in the format of YYYY/MM/file.txt
</p>
 
<a name="getting-file-meta-data"></a>
### Getting File Meta Data
To get information about the file:

```php
neon()->firefly->getMeta($uuid);
```

Will give:

```php
[
	"uuid" => "F9fe5-rueduefMbPoXDaL0",
	"name" => "Large archive file",
	"file_hash" => "b3283838041a9c8280ab62b700bccd51",
	"path" => "2016/12/Large archive file",
	"drive" => "local",
	"mime_type" => "application/zip",
	"size" => 281328512,
	"downloaded" => null,
	"viewed" => null,
	"updated_by" => null,
	"updated_at" => "2016-12-07 23:38:11",
	"created_by" => null,
	"created_at" => "2016-12-07 23:38:11",
	"deleted_at" => null
]
```

<p class="tip">
Note that the call to meta will not include the contents of the file.
You must specifically request the file contents via the 
<code>neon()->firefly->read($uuid)</code> function.
</p>

Also you may have noticed that the `uuid` is much smaller 
(13 characters smaller) than your default 36 character uuid.
We are using the `\neon\core\helpers\Hash::uuid64()` function to generate 
uuids in firefly this function generates a fixed 22 character long uuid.
The main reason is they are smaller especially when used in a URL. As the name 
implies we use base64 rather than base 16 to encode the uuid value. Thus giving us less characters but the same uniqueness.

Just a little comparison:

```php
'http://mydomain.com/id/aef123ab-13df-af12-ab34-13a34d5fa315'
// or with our new version
'http://mydomain.com/id/xjn99eAPciBfn45W4rjduL'
```

<a name="file-uploads"></a>
### File Uploads

Handling file uploads in web applications is a common use case.

```php
<?php
class profileController extends Controller 
{
	/**
	 * Upload a profile image action
 	 * @return Response 
 	 */
	public function actionProfileImage(Request $request)
	{
		if ($request->hasFile('my_file')) {
			
            // file is an instance of \neon\core\web\UploadedFile
            // Calling save will store the file using the default driver
			$uuid = $request->file('my_file')->save();
			
			// using fiefly directly
            $file = $request->file('my_file');
			$uuid = neon()->firefly->saveFile($file);
			
		}
	}
}
```


<a name="exposing-files-over-http"></a>
### Exposing Files Over Http

The FileManager aims to make exposing files over http easy.

If we have the File Manager key, which typically would be stored in the database.

We can call `$url = neon()->firefly->url($uuid)` to get the permalink to this file.

<a name="all-functions"></a>
### All Funcations 

A complete list of FileManager functions:

```php
// Save a file by passing the contents
neon()->firefly->save($contents, $name = null)
// Save a file
neon()->firefly->saveFile(UploadedFile $file)
// update an existing file
neon()->firefly->update($uuid, $contents)
// check if the file exists
neon()->firefly->exists($uuid)
// Get file meta data
neon()->firefly->meta($uuid)
// get the permalink url to the file
neon()->firefly->url($uuid)
// gets the visibility ('public' or 'private')
neon()->firefly->getVisibility($uuid)
// Get the visibility of a file - 'public' | 'private'
neon()->firefly->setVisibility($uuid, $visibility)
// Create a copy of a file (returns a new uuid)
neon()->firefly->copy($uuid)
// Soft deletes a file
neon()->firefly->delete($uuid)
// Restores a soft deleted file
neon()->firefly->restore($uuid)
// Destroys the file and removes it from the drive it is on
neon()->firefly->destroy($uuid)
// Prepend content to a file
neon()->firefly->prepend($uuid, $contents)
// Append the contents to a file
neon()->firefly->append($uuid, $contents);
// Read the file contents
neon()->firefly->read($uuid);
```

<a name="file-system"></a>
## Drive Manager - Consistent Cross Platform File System.

The Drive Manager manages drive objects that can interact with files and directories of various file system platforms for e.g. local disks, ftp, Amazon S3, Dropbox, etc. Files and directories are uniquely
referenced by their path.

```php
neon()->firefly->driveManager->drive()->put('some/file.txt', 'file contents');
```

```php
neon()->firefly->drive()->put('some/file.txt', 'file contents');
```

To read the contents of the file we need to specify the path

```php
neon()->firefly->drive()->read('some/file.txt');
```

<p class="tip">
Note that in the above code we do not specify a drive.
In this scenario the FileSystem will use the drive specified in its
default configuration property. Refering to the config example above
this would return the `media` drive.
</p>

<a name="the-local-drive"></a>
### The Local Drive

The local driver stores files in `@root/storage/` defined in the `root` 
configuration option. All file operations are relative to the `root` 
directory defined. Therefore, the following method would store the 
file in `@root/storage/my/file.txt`:

```php
neon()->firefly->drive('media')->put('my/file.txt', 'Contents');
```

<a name="the-db-drive"></a>
### The Db Drive

The db drive stores files in your configured database in the `firefly_files`
table using the `db` driver (don't be confused that both the drive and the driver are named the same in this example).

<a name="retrieving-files"></a>
### Retrieving Files

The `read` method may be used to retrieve the contents of a file. 
The raw string contents of the file will be returned by the method. 
Remember, all file paths should be specified relative to the "root" 
location configured for the drive:

```php
$contents = neon()->firefly->drive()->read('file.jpg');
```

The exists method determines if a file exists on the drive:

```php
$exists = neon()->firefly->drive('cloud')->exists('2016/11/mysql_backup.sql.gz');
// cloud is drive is configured to be powered by the s3 driver (referencing the dummy config above)
```
<a name="files-metadata"></a>
### File Metadata

In addition to reading and writing files, Neon can also provide information 
about the files themselves. For example, the size method may be used to get 
the size of the file in bytes:

```php
$size = neon()->firefly->drive()->getSize('me.jpg');
```

The lastModified method returns the UNIX timestamp of the last time the 
file was modified:

```php
$time = neon()->firefly->drive()->getLastModified('me.jpg');
```

Get an array of meta data - you can try these commands yourself in `./neo dev/shell`

```bash
>>> neon()->firefly->drive()->put('file.txt', 'test file');
=> true
>>> neon()->firefly->drive()->getMeta('file.txt');
=> [
     "type" => "file",
     "path" => "file.txt",
     "timestamp" => 1537197237,
     "size" => 7,
   ]
```

<a name="storing-files"></a>
### Storing Files

The put method may be used to store raw file contents on a drive. You may also 
pass a PHP resource to the put method, which will use Flysystem's underlying 
stream support. Using streams is greatly recommended when dealing with large 
files:

```php
neon()->firefly->drive()->put('file.jpg', $contents);

neon()->firefly->drive()->put('file.jpg', $resource);
```

<a name="automatic-streaming"></a>
### Automatic Streaming

Firefly automatically manage streaming a given file to your storage location using
the `put` method. This method accepts a php resource, a string of content or a
`neon\core\web\UploadedFile` instance or a standard php lib \SplFileInfo file object, well any object that implements  
the `getFilename` and `getRealPath` functions.
and will automatically stream the file to your desired location:


```php
// passing a resouce directly
neon()->firefly->drive()->put('my/file.txt', fopen('/path/file.txt', 'r+'));

neon()->firefly->drive()->put('my/file.txt', 'File contents');

neon()->firefly->drive()->put('my/file.txt', new UploadedFile($_FILES['my_file']));

// TODO: - currently the hash will break as its defined in UploadedFile only
// need to seperate this out.
neon()->firefly->drive()->put('my/file.txt', new \SplFileInfo('/path/to/photo'));
```

<a name="prepending-and-appening-to-files"></a>
### Prepending & Appending To Files

The prepend and append methods allow you to write to the beginning or end of a file:

```php
neon()->firefly->drive()->prepend('file.log', 'Prepended Text');

neon()->firefly->drive()->append('file.log', 'Appended Text');
```
<a name="copying-and-moving-files"></a>
### Copying & Moving Files

The copy method may be used to copy an existing file to a new location on the 
drive, while the move method may be used to rename or move an existing file to 
a new location:

```php
neon()->firefly->drive()->copy('old/file.jpg', 'new/file.jpg');

neon()->firefly->drive()->move('old/file.jpg', 'new/file.jpg');
```


<a name="image-manager"></a>
## Image Manager


| Name        | Function           | Description  |
| ------------- |:-------------:| -----:|
| Rotation      | turn | $1600 |
| col 2 is      | centered      |   $12 |
| zebra stripes | are neat      |    $1 |

Orientation	or	Rotates the image.	info
Crop	crop	Crops the image to specific dimensions.	info
Width	w	Sets the width of the image, in pixels.	info
Height	h	Sets the height of the image, in pixels.	info
Fit	fit	Sets how the image is fitted to its target dimensions.	info
Device pixel ratio	dpr	Multiples the overall image size.	info
Brightness	bri	Adjusts the image brightness.	info
Contrast	con	Adjusts the image contrast.	info
Gamma	gam	Adjusts the image gamma.	info
Sharpen	sharp	Sharpen the image.	info
Blur	blur	Adds a blur effect to the image.	info
Pixelate	pixel	Applies a pixelation effect to the image.	info
Filter	filt	Applies a filter effect to the image.	info
Watermark Path	mark	Adds a watermark to the image.	info
Watermark Width	markw	Sets the width of the watermark.	info
Watermark Height	markh	Sets the height of the watermark.	info
Watermark X-offset	markx	Sets the watermark distance from left/right edges.	info
Watermark Y-offset	marky	Sets the watermark distance from top/bottom edges.	info
Watermark Padding	markpad	Sets the watermark distance from the edges.	info
Watermark Position	markpos	Sets where the watermark is positioned.	info
Watermark Alpha	markalpha	Sets the watermark opacity.	info
Background	bg	Sets the background color of the image.	info
Border	border	Add a border to the image.	info
Quality	q	Defines the quality of the image.	info
Format	fm	Encodes the image to a specific format.	info




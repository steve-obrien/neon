The File Manager is responsible for abstracting file paths away and assigning files and managing files based on UUID's
It effectively adds an abstraction away from specific file paths.

It uses the driveManager service to achieve this.
The driveManager manages the different drives the system can talk to each of its configured drives implements a consistent **file system** interface across various platforms (local disks, amazon s3, ftp, etc etc)



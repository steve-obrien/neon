The File Manager is responsible for managing files based on their UUID's
It effectively adds an abstraction away from specific file paths.

It uses the filesystem service to achieve this.
The fileSystem service is a filesystem abstraction to provide a standard API across various file storage platforms - for e.g. local drives, Amazon s3, Ftp, etc etc.


REST API
========


## Retrieving Files

To display in a browser:

`http://domain.com/firefly/file/get?id=0Jy413n2e7q0eQImVBvsCg`

For example this would display the file if it is an image

```html
<img src="http://domain.com/firefly/file/get?id=0Jy413n2e7q0eQImVBvsCg" />
```
Note: you should really use firefly/file/img if you know you intend to display an image

To force a download dialog:

`http://domain.com/firefly/file/get?id=0Jy413n2e7q0eQImVBvsCg&download=true`

### Content Disposition
The Firefly file URLs will respect browser settings by default.
Most browsers will open files that they can and download the rest.
If you want it to download and give the user a 'save as' dialog, you can attach the `'?download=true'` or shorthand `'?dl=true'` parameter.

```bash
curl -X GET "http://domain.com/firefly/file/get?id=0Jy413n2e7q0eQImVBvsCg&download=true"
```

## Metadata

Get the meta information of a file.

```bash
curl -X GET 'http://domain.com/firefly/api/file/meta?id=0Jy413n2e7q0eQImVBvsCg'
```

Example result:

```json
{
	"uuid": "0Jy413n2e7q0eQImVBvsCg",
	"name": "partner_600x395.jpg",
	"file_hash": null,
	"path": "2017/01/partner_600x395.jpg",
	"drive": "local",
	"mime_type": "image/jpeg",
	"size": 96408,
	"downloaded": 0,
	"viewed": 0,
	"updated_by": 1,
	"updated_at": "2017-01-02 19:54:00",
	"created_by": 1,
	"created_at": "2017-01-02 19:54:00",
	"deleted_at": null,
	"id": "0Jy413n2e7q0eQImVBvsCg"
}
```

`name`: This is the file name  
`file_hash`: an md5 hash of the file contents - this will be present only if previously requested  
`path`: this is where the raw file is currently located on the `drive`  
`drive`: The drive represents a file system drive and could be `s3` | `local` or other values  
`mime_type`: The mime type of the file  
`size`: The file size in bytes  

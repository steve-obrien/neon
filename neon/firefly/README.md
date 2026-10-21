# Firefly
Firefly is the neon system for managing files.

## Hardening Firefly
The following optional settings have been introduced to env.ini in order to harden Firefly:
NEON_FIREFLY_API_DISABLED : disable the API entirely (will likely break the default neon admin area!)   
NEON_FIREFLY_API_UPLOAD_FROM_URL_DISABLED : disable uploading from remote URL (an API user can perform this themselves)   
NEON_FIREFLY_FILE_GET_ONLY_ACCEPT_UUIDS : Firefly GET requests only accept UUIDs (not file/remote ids)   
NEON_FIREFLY_FILE_IMG_ONLY_ACCEPT_UUIDS : Firefly image endpoints only accepts UUIDs   
NEON_FIREFLY_SAFE_FILENAMES_ONLY : validate/assets safe filenames   
NEON_FIREFLY_SAFE_PATHS_ONLY : validate/assets safe paths   
NEON_FIREFLY_SAFE_FILE_REALPATHS_ONLY : ensure realpath is applied to file names    
NEON_FIREFLY_EXTENSION_WHITELIST = comma-separated whitelist of allowed filename extensions (\*)   
NEON_FIREFLY_EXTENSION_ALLOW_NONE  = don't allow filenames without an extension   
NEON_FIREFLY_REQUEST_CONTENT_TYPE_WHITELIST = comma-separated whitelist of allowed request content types (\*)   
NEON_FIREFLY_REQUEST_CONTENT_TYPE_ALLOW_NONE = don't allow requests without a content type   
NEON_FIREFLY_DETECT_MIME_TYPE_WHITELIST = comma-separated whitelist of allowed (detected) file mime-types   
NEON_FIREFLY_DETECT_MIME_TYPE_ALLOW_NONE = don't allow requests without a detected file mime-type   
NEON_FIREFLY_ASSERT_RECREATE_RASTER_IMAGE = for raster images attempt to recreate the image via Intervention!

(\*) - note that these can be spoofed by a client!

> Notes:
> - particular care should be taken with SVGs since they permit both JavaScript & local file references
> - tightening images to far may well break neon admin area file-handling if care is not taken
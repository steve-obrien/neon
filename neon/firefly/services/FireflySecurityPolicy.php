<?php

namespace neon\firefly\services;

use Intervention\Image\Facades\Image;
use neon\core\helpers\File;

class FireflySecurityPolicy
{
	private const API_DISABLED = "NEON_FIREFLY_API_DISABLED";
	private const API_UPLOAD_FROM_URL_DISABLED = "NEON_FIREFLY_API_UPLOAD_FROM_URL_DISABLED";

	private const FILE_GET_ONLY_ACCEPT_UUIDS = "NEON_FIREFLY_FILE_GET_ONLY_ACCEPT_UUIDS";
	private const FILE_IMG_ONLY_ACCEPT_UUIDS = "NEON_FIREFLY_FILE_IMG_ONLY_ACCEPT_UUIDS";
	private const SAFE_PATHS_ONLY = "NEON_FIREFLY_SAFE_PATHS_ONLY";
	private const SAFE_FILENAMES_ONLY = "NEON_FIREFLY_SAFE_FILENAMES_ONLY";
	private const SAFE_FILE_REALPATHS_ONLY = "NEON_FIREFLY_SAFE_FILE_REALPATHS_ONLY";

	private const EXTENSION_WHITELIST = "NEON_FIREFLY_EXTENSION_WHITELIST";
	private const EXTENSION_DISALLOW_NONE = "NEON_FIREFLY_EXTENSION_DISALLOW_NONE";

	private const REQUEST_CONTENT_TYPE_WHITELIST = "NEON_FIREFLY_REQUEST_CONTENT_TYPE_WHITELIST";
	private const REQUEST_CONTENT_TYPE_DISALLOW_NONE = "NEON_FIREFLY_REQUEST_CONTENT_TYPE_DISALLOW_NONE";

	private const DETECT_MIME_TYPE_WHITELIST = "NEON_FIREFLY_DETECT_MIME_TYPE_WHITELIST";
	private const DETECT_MIME_TYPE_DISALLOW_NONE = "NEON_FIREFLY_DETECT_MIME_TYPE_DISALLOW_NONE";

	private const ASSERT_RECREATE_RASTER_IMAGE = "NEON_FIREFLY_ASSERT_RECREATE_RASTER_IMAGE";

	private const ALL_POLICIES = [
		self::API_DISABLED,
		self::API_UPLOAD_FROM_URL_DISABLED,
		self::FILE_GET_ONLY_ACCEPT_UUIDS,
		self::FILE_IMG_ONLY_ACCEPT_UUIDS,
		self::SAFE_PATHS_ONLY,
		self::SAFE_FILENAMES_ONLY,
		self::SAFE_FILE_REALPATHS_ONLY,
		self::EXTENSION_WHITELIST,
		self::EXTENSION_DISALLOW_NONE,
		self::REQUEST_CONTENT_TYPE_WHITELIST,
		self::REQUEST_CONTENT_TYPE_DISALLOW_NONE,
		self::DETECT_MIME_TYPE_WHITELIST,
		self::DETECT_MIME_TYPE_DISALLOW_NONE,
		self::ASSERT_RECREATE_RASTER_IMAGE
	];

	private static function getEnvValue(string $policy, mixed $default = false): mixed
	{
		if (!in_array($policy, self::ALL_POLICIES, true)) {
			throw new \InvalidArgumentException("Unrecognised Firefly policy: $policy");
		}
		return env($policy, $default);
	}

	public static function isApiDisabled(): bool {
		return self::getEnvValue(self::API_DISABLED);
	}

	public static function isApiUploadFromUrlDisabled(): bool
	{
		return self::getEnvValue(self::API_UPLOAD_FROM_URL_DISABLED);
	}

	public static function fileGetOnlyAcceptsUuids(): bool
	{
		return self::getEnvValue(self::FILE_GET_ONLY_ACCEPT_UUIDS);
	}

	public static function fileImageOnlyAcceptsUuids(): bool
	{
		return self::getEnvValue(self::FILE_IMG_ONLY_ACCEPT_UUIDS);
	}

	public static function safePathsOnly(): bool
	{
		return self::getEnvValue(self::SAFE_PATHS_ONLY);
	}

	public static function safeFilenamesOnly(): bool
	{
		return self::getEnvValue(self::SAFE_FILENAMES_ONLY);
	}

	public static function safeFileRealpathsOnly(): bool
	{
		return self::getEnvValue(self::SAFE_FILE_REALPATHS_ONLY);
	}

	public static function assertWhitelistedExtension(string $filename): void
	{
		$filename = File::getSafeUploadFilename($filename);
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if ($extension === '') {
			if (!self::getEnvValue(self::EXTENSION_DISALLOW_NONE)) {
				return;
			}
			throw new \InvalidArgumentException('Uploaded file must have an extension.');
		}

		$whitelist = self::getEnvValue(self::EXTENSION_WHITELIST);
		if (!$whitelist) {
			return;
		}
		$whitelistArray = self::getArrayFromString($whitelist);
		if (!$whitelistArray) {
			return;
		}

		if (!in_array($extension, $whitelistArray, true)) {
			throw new \InvalidArgumentException("File extension '{$extension}' is not allowed.");
		}
	}

	public static function assertRequestContentType(string $contentType): void
	{
		$contentType = trim(strtolower($contentType));
		if ($contentType === "") {
			if (!self::getEnvValue(self::REQUEST_CONTENT_TYPE_DISALLOW_NONE)) {
				return;
			}
			throw new \InvalidArgumentException('Uploaded file must have a content-type extension.');
		}

		$whitelist = self::getEnvValue(self::REQUEST_CONTENT_TYPE_WHITELIST);
		if (!$whitelist) {
			return;
		}
		$whitelistArray = self::getArrayFromString($whitelist);
		if (!$whitelistArray) {
			return;
		}
		if (!in_array($contentType, $whitelistArray, true)) {
			throw new \InvalidArgumentException("Request content-type '{$contentType}' is not allowed.");
		}
	}

	public static function detectedMimeTypeWhitelistEnabled() {
		return (bool)self::getEnvValue(self::DETECT_MIME_TYPE_WHITELIST);
	}

	/**
	 * todo: - this could also be checking for extension/content-type mismatch but that's a fairly significant refactor
	 */
	public static function assertDetectedMimetype($filename): ?string
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo === false) {
			throw new \RuntimeException('Could not initialise fileinfo.');
		}
		try {
			$mimetype = finfo_file($finfo, $filename);
			self::assertDetectedMimetypeAgainstWhitelist($mimetype);
		} finally {
			finfo_close($finfo);
		}
		return !$mimetype ? null : $mimetype;
	}

	public static function assertDetectedMimetypeFromStream($stream): ?string
	{

		if (!is_resource($stream)) {
			throw new \InvalidArgumentException('Invalid stream');
		}

		$sample = stream_get_contents($stream, 8192, 0);
		if ($sample === false || $sample === '') {
			if (!self::getEnvValue(self::DETECT_MIME_TYPE_DISALLOW_NONE)) {
				return null;
			}
			throw new \InvalidArgumentException('Uploaded file must have a detectable mime-type.');
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo === false) {
			throw new \RuntimeException('Could not initialise fileinfo.');
		}

		try {
			$mimetype = finfo_buffer($finfo, $sample);
			self::assertDetectedMimetypeAgainstWhitelist($mimetype);
		} finally {
			finfo_close($finfo);
		}
		rewind($stream);
		return !$mimetype ? null : $mimetype;
	}

	public static function isRasterImageMimetype(?string $mimetype): bool
	{
		switch ($mimetype) {
			// very conservative for now!
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
			case 'image/webp';
				return true;
			default:
				return false;
		}
	}

	public static function assertRecreateRasterImage(string $filePath): void
	{
		if (!self::getEnvValue(self::ASSERT_RECREATE_RASTER_IMAGE)) {
			return;
		}
		if (!is_file($filePath) || !is_readable($filePath)) {
			throw new \InvalidArgumentException('Uploaded image cannot be read.');
		}
		try {
			Image::make($filePath)
				->orientate()
				->save($filePath);
		}
		catch (\Throwable $e) {
			neon()->errorHandler->logException($e);
			throw new \InvalidArgumentException("Uploaded image is invalid.");
		}
	}

	private static function assertDetectedMimetypeAgainstWhitelist($mimetype)
	{
		if ($mimetype !== false) {
			$mimetype = strtolower(trim($mimetype));
		}

		if (!$mimetype) {
			if (self::getEnvValue(self::DETECT_MIME_TYPE_DISALLOW_NONE)) {
				return $mimetype;
			}
			throw new \InvalidArgumentException('Uploaded file must have a detectable mime-type.');
		}
		$whitelist = self::getEnvValue(self::DETECT_MIME_TYPE_WHITELIST);
		if (!$whitelist) {
			return $mimetype;
		}
		$whitelistArray = self::getArrayFromString($whitelist);
		if (!$whitelistArray) {
			return $mimetype;
		}
		if (!in_array($mimetype, $whitelistArray, true)) {
			throw new \InvalidArgumentException("Detected mime-type '{$mimetype}' is not allowed.");
		}
		return $mimetype;
	}

	private static function getArrayFromString($fromString, $separator=",")
	{
		$array = array_filter(array_map('trim', explode($separator, $fromString)));
		return array_map('strtolower', $array);
	}
}
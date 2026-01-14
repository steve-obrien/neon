<?php

namespace neon\cms\services\cmsManager\interfaces;

/**
 * This provides access to the static data within CosMoS.
 */
interface ICmsStaticData
{
	/**
	 * edit a static piece of content
	 * This will be created if it doesn't already exist in the database
	 *
	 * @param string $key  the key for the content
	 * @param null|string $pageId  the pageId if this is page specific or null if not
	 * @param string $content  the new content
	 * @return bool|array  returns true if successful and if not the errors
	 */
	public function editStaticContent($key, $pageId, $content);

	/**
	 * Get the static content given a particular content key and optional
	 * pageId combo
	 *
	 * @param string $key  the key for the content
	 * @param null|string $pageId  the pageId if this is page specific or null if not
	 * @return string  the content
	 */
	public function getStaticContent($key, $pageId);

	/**
	 * Get static content for a whole set of requests at once
	 *
	 * @param array $requests  Each request is an array of
	 *   'id'=>['key'=>key, 'pageId'=>pageId|null]
	 *   where id is the id obtained from @see bulkCreateStaticContentIds
	 * @return array  Each result is returned as
	 *   'id'=>null  if the data wasn't found
	 *   'id'=>['content' => the content] if it was
	 *   where the id is as provided in the original request
	 */
	public function bulkStaticContentRequest($requests);

	/**
	 * Create a static content request id based on the request
	 * @param array $request  ['key'=>key, 'pageId'=>pageId|null]]
	 * @return string  the id
	 * @see bulkCreateStaticContentIds for doing this to an array of requests
	 */
	public function createStaticContentId($request);

	/**
	 * Create a static content request id for each request in
	 * a set of requests
	 *
	 * @param array &$requests  Each request is an array of
	 *   [n]=>['key'=>key, 'pageId'=>pageId|null], ...] which will be
	 *   converted into a unique id for use in @see bulkStaticContentRequest
	 * @return array  an array of
	 *   ['id'] => ['key'=>key, 'pageId'=>pageId|null], ...]
	 */
	public function bulkCreateStaticContentIds($requests);

}
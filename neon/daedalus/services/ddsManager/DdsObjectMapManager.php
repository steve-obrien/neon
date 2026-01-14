<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\services\ddsManager;

use neon\daedalus\interfaces\IDdsObjectMapManagement;

use neon\daedalus\services\ddsManager\DdsCore;
use neon\daedalus\services\ddsManager\models\DdsMember;
use neon\daedalus\services\ddsManager\models\DdsObject;
use neon\daedalus\services\ddsManager\models\DdsLink;
use neon\core\helpers\Arr;

class DdsObjectMapManager extends DdsCore implements IDdsObjectMapManagement
{
	/**
	 * Caches for results from queries so can protect against over-zealous
	 * requests to the database within one call
	 * @var array
	 */
	private static $_mapResultsCache=[];
	/**
	 * The set of map requests
	 * An array with the requests and the maximum number in the chain
	 * @var array
	 */
	private static $_mapRequests = ['keys'=>[], 'chains'=>[], 'depth'=>0];
	/**
	 * The set of map results
	 * @var array of ['key'=>[chain]
	 */
	private static $_mapResults = [];
	/**
	 * The set of multiMapRequests which store individual map requests
	 * @var array of ['requestKey' => ['subRequestKey']]
	 */
	private static $_multiMapRequests = [];

	private static $_defaultMapChain = '__DEFAULT_MAP_CHAIN__';

	/**
	 * @inheritdoc
	 */
	public function getObjectMap($classType, $filters=[], $fields=[], $start=0, $length=1000, $includeDeleted=false)
	{
		if (!$classType)
			throw new \InvalidArgumentException("Invalid classType '$classType' in getObjectMap. Correct calling code");

		$cacheKey = md5(serialize(func_get_args()));
		if (empty(self::$_mapResultsCache[$cacheKey])) {
			try {
				$limit = ['start' => max(0, (integer)$start), 'length' => min(1000, (integer)$length)];
				$classType = $this->canonicaliseRef($classType);
				$fieldsClause = '';
				$linkJoins = [];
				if ($fields) {
					$getFields = [];
					$field2link = [];
					$linkedMemberRows = DdsMember::find()
						->select(['member_ref', 'link_class', 'data_type_ref'])
						->where(['class_type'=>$classType, 'data_type_ref' => ['link_uni', 'link_multi'], 'member_ref'=>$fields, 'deleted' => 0])
						->asArray()->all();
					$linkedMembers = Arr::index($linkedMemberRows, 'member_ref');
					if ($linkedMemberRows) {
						$linkedMemberClasses = DdsMember::find()
							->select(['class_type', 'member_ref'])
							->where(['class_type'=>Arr::pluck($linkedMemberRows, 'link_class'), 'map_field'=>1, 'deleted' => 0])
							->asArray()->all();
						$class2Member = Arr::index($linkedMemberClasses, 'class_type');
						foreach ($linkedMembers as $lm) {
							// only try to link through to tables that are within daedalus
							if (isset($class2Member[$lm['link_class']]))
								$field2link[$lm['member_ref']] = $class2Member[$lm['link_class']];
						}
					}
					foreach ($fields as $field) {
						if (array_key_exists($field, $field2link)) {
							$fl = $field2link[$field];
							$table = $this->getTableFromClassType($fl['class_type']);
							$getFields[] = "`$table`.`$fl[member_ref]`";
							if ($linkedMembers[$field]['data_type_ref'] == 'link_uni')
								$linkJoins[$table] = "LEFT JOIN `$table` ON `t`.`$field`=`$table`.`_uuid`";
							else {
								$linkName = 'link'.$field;
								$linkJoins[$linkName] = "LEFT JOIN `dds_link` `$linkName` ON (`o`.`_uuid`=`$linkName`.`from_id` AND `$linkName`.`from_member`='$field')";
								$linkJoins[$table] = "LEFT JOIN `$table` ON `$linkName`.`to_id` =`$table`.`_uuid`";
							}
						} else {
							$getFields[] = "`t`.`$field`";
						}
					}
					$fieldsClause = implode(', ', $getFields);
				} else {
					$member = $this->getMapMemberForClass($classType);
					if (!$member)
						throw new \RuntimeException("There is no mappable member field set on class $classType");
					// set this as an array for later
					$fields = [$member['member_ref']];
					$fieldsClause = "`t`.`$member[member_ref]`";
				}
				$map = [];
				$notDeleted = $includeDeleted ? '' : 'AND `o`.`_deleted`=0';
				$table = $this->getTableFromClassType($classType);
				// set up any additional filtering
				$filterClause = '';
				$filterValues = [];
				if ($filters) {
					$ddsObjectFields = ['_uuid','_created','_deleted','_updated'];
					// whitelist filter keys to prevent sql injection
					$members = $this->getClassMembers($classType);
					$filterSubClause = [];
					foreach ($filters as $field=>$value) {
						if ($field == '_map_field_') {
							$mapMember = $this->getMapMemberForClass($classType);
							if ($mapMember && $value)
								$filterSubClause[] = "AND `t`.`$mapMember[member_ref]` LIKE '%$value%'";
							continue;
						}
						if (!(isset($members[$field]) || in_array($field, $ddsObjectFields)))
							continue;
						$keyClause = (in_array($field, $ddsObjectFields)) ? "`o`.`$field`" : "`t`.`$field`";
						$value = is_array($value) ? $value : [$value];
						$vcount = 1;
						$inClause = [];
						foreach ($value as $v) {
							$inClause[] = ":$field$vcount";
							$filterValues[":$field$vcount"] = $v;
							$vcount++;
						}
						$inClause = implode(',',$inClause);
						if (!empty($members[$field]) && $members[$field]['data_type_ref'] == 'link_multi') {
							$linkName = "link$field";
							if (!isset($linkJoins[$linkName]))
								$linkJoins[$linkName] = "LEFT JOIN `dds_link` `$linkName` ON (`o`.`_uuid`=`$linkName`.`from_id` AND `$linkName`.`from_member`='$field')";
							$filterSubClause[] = "AND `$linkName`.`to_id` IN ($inClause)";
						} else {
							$filterSubClause[] = "AND $keyClause IN ($inClause)";
						}
					}
					$filterClause = implode (' ', $filterSubClause);
				}
				$linkJoinsClause = implode(' ', $linkJoins);

				$query = "SELECT DISTINCT `o`.`_uuid`, $fieldsClause FROM `dds_object` `o` LEFT JOIN  `$table` `t` ON `o`.`_uuid`=`t`.`_uuid` " .
					"$linkJoinsClause WHERE `o`.`_class_type` = '$classType' $filterClause $notDeleted ORDER BY $fieldsClause " .
					"LIMIT $limit[start], $limit[length]";
				$command = neon()->db->createCommand($query);
				if ($filters)
					$command->bindValues($filterValues);
				$rows = $command->queryAll();
				foreach ($rows as $r) {
					// extract out the uuid result
					$uuid = $r['_uuid'];
					unset($r['_uuid']);

					// set the map
					$map[$uuid] = (count($fields)==1) ? current($r) : $r;
				}
				self::$_mapResultsCache[$cacheKey] = $map;
			} catch (\Exception $e) {
				$errorMessage = "Exception during getting a map. This may be caused by empty filters - you cannot filter on null. ".
					"Your filters were: ".print_r($filters,true).". The error message is ".$e->getMessage();
				\Neon::error($errorMessage, 'DDS');
				debug_message($errorMessage);
				return [];
			}
		}
		return self::$_mapResultsCache[$cacheKey];
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapLookupRequest($objectUuids, $chainMapFields=[], $classType=null)
	{
		if (empty($objectUuids))
			return null;
		/*  See if there are any linked fields that we need to get
			The implementation here is somewhat crude. Generally speaking we are probably looking
			at needing to implement graphql or something similar to easily manage all the possible
			ways in which we might want to handle linked data through a request. For now if we need to
			link from one table to another, the simplest is to do it as another map chain request
			and merge them all together at the end when getting results.
		*/
		$linkedMembers = [];
		if ($classType && $chainMapFields) {
			$linkMemberKeys = array_keys($this->getClassMembers($classType, ['link_uni']));
			$linkedMembers = array_values(array_intersect($chainMapFields, $linkMemberKeys));
		}
		$requestKeys = [];
		// make the initial request key
		$requestKeys[] = $this->makeMapChainLookupRequest($objectUuids, null, [$chainMapFields]);
		// now make one for each linked member
		foreach ($linkedMembers as $lm) {
			$requestKeys[] = $this->makeMapChainLookupRequest($objectUuids, [$lm]);
		}
		return implode('|',$requestKeys);
	}

	/**
	 * @inheritdoc
	 */
	public function getMapLookupResults($requestKey)
	{
		$requestKeys = explode('|', $requestKey ?? '');
		$partials = [];
		foreach ($requestKeys as $rk) {
			$partials[] = $this->getMapChainLookupResults($rk);
		}
		if (count($requestKeys) > 1) {
			// skip over the first partial as this is our map
			// and look through others to fill back in
			foreach ($partials[0] as $u=>$p) {
				foreach ($p as $i=>$v) {
					if ($this->isUuid($v)) {
						for ($i=1; $i<count($requestKeys); $i++) {
							if (array_key_exists($v, $partials[$i][1])) {
								$partials[0][$u][$i] = $partials[$i][1][$v];
								continue;
							}
						}
					}
				}
			}
		}
		return $partials[0];
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapChainLookupRequest($objectUuids, $chainMembers=[], $chainMapFields=[], $inReverse=false)
	{
		if (empty($objectUuids))
			return null;

		// create a request key based on the input parameters
		$requestKey = md5(serialize(func_get_args()));

		// handle this request if we haven't already done so
		if (
			!isset(static::$_mapRequests[$requestKey])
			&& !isset(static::$_mapResults[$requestKey])
		) {
			$this->addRequest(
				$requestKey,
				$objectUuids,
				$chainMembers,
				$chainMapFields,
				$inReverse
			);
		}

		return $requestKey;
	}

	/**
	 * @inheritdoc
	 */
	public function commitMapRequests()
	{
		$requests = &static::$_mapRequests;
		// check we have any requests to make
		if (count($requests['chains']) === 0)
			return;
		// run through the chain and get the reverse maps for each level of requests
		foreach ($requests['chains'] as $i=>&$chain) {
			// the requests will be filled in during this call
			$this->getMapLookupByLevel($chain);
			foreach ($chain as $k=>$r) {
				if ($r['next_uuid'])
					$requests['chains'][($i+1)][$k]['uuid'] = $r['next_uuid'];
			}
		}
		$this->convertMapRequestResults();
		$this->resetMapRequests();
	}

	/**
	 * @inheritdoc
	 */
	public function getMapChainLookupResults($requestKey)
	{
		// first commit any pending requests and then check results
		$this->commitMapRequests();

		// temporary while we are dealing with multiple uuid requests as
		// a set of single map requests
		if (isset(static::$_multiMapRequests[$requestKey])) {
			return $this->getMultiMapResults($requestKey);
		}

		return (!empty(static::$_mapResults[$requestKey])
			? static::$_mapResults[$requestKey]
			: []);
	}

	/**
	 * @inheritdoc
	 */
	public function clearMapRequests()
	{
		$this->resetMapRequests();
		static::$_mapResults = [];
	}


	// ----------------------
	// Implementation Methods


	/**
	 * This makes a query for all of the maps at a particular request level and
	 * pushes the replies back into the requests themselves ready for extraction
	 * later into the results
	 *
	 * @param array &$requests
	 */
	private function getMapLookupByLevel(&$requests)
	{
		if (empty($requests))
			return;

		$endpoints = [];
		$requestsByUuid = [];
		foreach ($requests as $r) {
			$endpoints[] = $r['uuid'];
			$requestsByUuid[$r['uuid']][] = $r;
		}
		$objectRows = DdsObject::find()
			->select(['_uuid','_class_type'])
			->where(['_uuid'=>$endpoints])
			->andWhere(['_deleted'=>0])
			->asArray()
			->all();
		$maps = [];
		$hackySeparator = '§|§@§|§'; // use a horrible unlikely separator to manage multiple map field requests
		if (!empty($objectRows)) {
			$classTypes = []; // get a list of the required class types
			$objectsByClassMapsAndChain = []; // get all ids for the same class combo
			foreach ($objectRows as $o) {
				// get the chain field on the object
				foreach ($requestsByUuid[$o['_uuid']] as $r) {
					$objectsByClassMapsAndChain[$o['_class_type']][$r['chain']][$r['mapFields']][$o['_uuid']] = $o['_uuid'];
				}
				$classTypes[$o['_class_type']] = $o['_class_type'];
			}

			// now find all the maps members for the required classes
			$mapFields = DdsMember::find()
				->select(['class_type','member_ref'])
				->where(['class_type'=>$classTypes, 'deleted'=>0, 'map_field'=>1])
				->asArray()
				->all();
			$mapsByClass = [];
			foreach ($mapFields as $m) {
				$mapsByClass[$m['class_type']] = $m['member_ref'];
			}

			// now find all of the objects for the level up in the chain
			$subQuery = [];
			$chainValues = [];
			$count = 0;
			foreach ($objectsByClassMapsAndChain as $class => $chainMapObjects) {
				foreach ($chainMapObjects as $chain => $mapObjects) {
					foreach ($mapObjects as $mapField => $objectUuids) {
						$objs = "'".implode("','",$objectUuids)."'";
						// make sure a map field can be created
						$mapFieldsClause = '';
						if ($mapField === self::$_defaultMapChain) {
							if (!isset($mapsByClass[$class]))
								continue;
							$mapFieldsClause = "CAST(`$mapsByClass[$class]` AS CHAR)";
						} else {
							$mapFieldsClause = "CONCAT_WS('$hackySeparator'";
							foreach (explode(',',$mapField) as $m) {
								$mapFieldsClause .= ",CAST(`$m` AS CHAR)";
							}
							$mapFieldsClause .= ')';
						}
						$nextUuidClause = ($chain == '__FINAL__' ? "null" : "`$chain`").' AS `next_uuid`';
						$subQuery[] = <<<EOQ
	SELECT `_uuid` as `uuid`, '$mapField' AS `mapFields`, $mapFieldsClause AS `map`, '{$chain}' AS `chain`, $nextUuidClause FROM `ddt_$class` WHERE `_uuid` IN ($objs)
EOQ;
						$chainValues[":CHAIN$count"] = $chain;
						$count++;
					}
				}
			}
			$query = implode(' UNION ', $subQuery);
			$command = neon()->db->createCommand($query);

			// get the maps
			$maps = $command->queryAll();
		}

		// sort the maps by chain and uuid
		$mapsByChainAndUuid = [];
		foreach ($maps as $m) {
			if (strpos($m['map'] ?? '', $hackySeparator) !== false) {
				$m['map'] = explode($hackySeparator,$m['map']);
			}
			$mapsByChainAndUuid[$m['chain']][$m['mapFields']][$m['uuid']] = $m;
		}

		// now backfill the results
		foreach ($requests as &$r) {
			if (isset($mapsByChainAndUuid[$r['chain']][$r['mapFields']][$r['uuid']])) {
				$map = $mapsByChainAndUuid[$r['chain']][$r['mapFields']][$r['uuid']];
				$r['map'] = $map['map'];
				$r['next_uuid'] = $map['next_uuid'];
			} else {
				$r['next_uuid'] = null;
			}
		}
	}

	/**
	 * Add a request to the set of map requests
	 *
	 * Requests are split into a series of requests at each chain level so
	 * that we can make all the requests for one level at the same time. This
	 * way the total number of requests is limited to the maximum number of
	 * levels in the chain rather than a sum of (request[i]*length[i]) across
	 * each request
	 *
	 * @param string $requestKey
	 * @param string|array $objectUuids  string for single object and array for multiple
	 * @param null|array $chainMembers  null to mean not a chain
	 * @param array $chainMapFields
	 * @param boolean $inReverse
	 */
	private function addRequest($requestKey, $objectUuid, $chainMembers, $chainMapFields, $inReverse)
	{
		if (empty($objectUuid))
			return;

		// Treat a request for objectUuids as multiple single object map requests
		// TODO NJ 20190415 - make this work properly with multiple uuids without needing multiple requests
		if (is_array($objectUuid)) {
			foreach ($objectUuid as $uuid) {
				$subRequestKey = $requestKey.$uuid;
				$this->addRequest(
					$subRequestKey,
					$uuid,
					$chainMembers,
					$chainMapFields,
					$inReverse
				);
				static::$_multiMapRequests[$requestKey][] = $subRequestKey;
			}
			return;
		}

		// see if this is a chain or not
		$chainable = true;
		if ($chainMembers === null) {
			$chainMembers = [];
			$chainable = false;
		}

		$requests = &static::$_mapRequests; // to make the code slightly shorter

		// check to see if the request has already added
		if (isset($requests['keys'][$requestKey]))
			return;

		// calculate how many chains we are going down
		$chainCount = count($chainMembers);
		// split the chain up into requests per chain level if there are any
		foreach ($chainMembers as $i=>$mc) {
			$mapFieldKey = ($i == 0 ? 0 : $chainMembers[$i-1]);
			$mapFields = !empty($chainMapFields[$mapFieldKey]) ? implode(',',$chainMapFields[$mapFieldKey]) : self::$_defaultMapChain;
			$requests['chains'][$i][$requestKey] = ['uuid'=>($i===0?$objectUuid:null), 'map'=>null, 'mapFields'=>$mapFields, 'chain'=>$mc, 'key'=>$requestKey];
		}
		// add a final chain for the last item
		$mapFieldKey = ($chainCount == 0 ? 0 : $chainMembers[$chainCount-1]);
		$mapFields = !empty($chainMapFields[$mapFieldKey]) ? implode(',',$chainMapFields[$mapFieldKey]) : self::$_defaultMapChain;
		$requests['chains'][$chainCount][$requestKey] = ['uuid'=>($chainCount===0?$objectUuid:null), 'map'=>null, 'mapFields'=>$mapFields, 'chain'=>'__FINAL__', 'key'=>$requestKey];

		// and add the requestKey so we don't calculate this again
		$requests['keys'][$requestKey] = [
			'objectUuid' => $objectUuid,
			'inChains'=>$chainable,
			'inReverse'=>($chainable && $inReverse),
		];
	}

	/**
	 * Reset the map requests so new requests can be added
	 */
	private function resetMapRequests()
	{
		static::$_mapRequests = ['keys'=>[], 'chains'=>[], 'depth'=>0];
		static::$_multiMapRequests = [];
	}

	/**
	 * Convert requests results into results expected for mappings
	 */
	private function convertMapRequestResults()
	{
		$keys = &static::$_mapRequests['keys'];
		$chains = &static::$_mapRequests['chains'];
		$results = &static::$_mapResults;

		$tempResults = [];
		if (count($chains)===0)
			return;
		foreach ($chains as $level => $maps) {
			foreach ($maps as $key=>$map) {
				$tempResults[$key][$level][$map['uuid']] = $map['map'];
			}
		}

		// make any conversions to the results according to the request settings
		foreach ($keys as $k=>$settings) {
			if ($settings['inChains'] == true) {
				$results[$k] = ($settings['inReverse']) ? array_reverse($tempResults[$k]) : $tempResults[$k];
			} else {
				// in this case we just need the lowest level map within the chain
				$results[$k] = $tempResults[$k][0];
			}
		}

		// sort out any multimaps that need to be converted
		// TODO NJ 20190415 Remove the need for this once all converted into
		// initial query
		foreach (static::$_multiMapRequests as $requestKey=>$subRequestKeys) {
			$multiMapResult = [];
			foreach ($subRequestKeys as $subRequestKey) {
				if (isset($results[$subRequestKey])) {
					$r = $results[$subRequestKey];
					$settings = $keys[$subRequestKey];
					if ($settings['inChains']) {
						// set results as [['uuid'] => chain,...]
						$multiMapResult[$settings['objectUuid']] = $r;
					} else {
						// set results as [uuid=>map,...]
						$multiMapResult[key($r)] = current($r);
					}
				}
			}
			// and store these results onto the total results
			$results[$requestKey] = $multiMapResult;
		}
	}
}

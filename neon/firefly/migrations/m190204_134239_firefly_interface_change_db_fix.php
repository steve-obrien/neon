<?php

use yii\db\Migration;

/**
 * Class m190204_134239_firefly_interface_change_db_fix
 * Converts old API firefly action links to new api links.
 * These become hardcoded when adding images to wysiwyg
 */
class m190204_134239_firefly_interface_change_db_fix extends Migration
{
	/**
	 * This is the old api url that will no longer exist
	 * @var string
	 */
	private $upSearch = '/firefly/get/img';
	/**
	 * This is the new api link to convert to
	 * @var string
	 */
	private $upReplace = '/firefly/file/img';

	public function safeUp()
	{
		return $this->convertFireflyGetsToFiles($this->upSearch, $this->upReplace);
	}

	public function safeDown()
	{
		return $this->convertFireflyGetsToFiles($this->upReplace, $this->upSearch);
	}

	private function convertFireflyGetsToFiles($search, $replace)
	{
		$potentiallyAffected = $this->db->createCommand("SELECT `class_type`, `member_ref` FROM `dds_member` WHERE `data_type_ref`='textlong'")->queryAll();
		if (count($potentiallyAffected)==0)
			return;
		$classes = [];
		foreach ($potentiallyAffected as $pa)
			$classes[$pa['class_type']][] = $pa['member_ref'];
		$phoebeClasses = array_keys($classes);
		$phoebe = neon('phoebe')->getIPhoebeType('daedalus');

		$phoebeDefinition = [];
		foreach ($phoebeClasses as $pc)
			$phoebeDefinition[$pc] = $phoebe->getClass($pc, null, false);

		$potentiallyAffectedLookup = [];
		foreach ($potentiallyAffected as $pa) {
			if (empty($potentiallyAffectedLookup[$pa['class_type']]))
				$potentiallyAffectedLookup[$pa['class_type']] = [];
			$potentiallyAffectedLookup[$pa['class_type']][] = $pa['member_ref'];
		}

		$wysiwygFields = [];
		foreach ($phoebeDefinition as $pdKey => $pd) {
			$fields = !empty($pd->definition['fields']) ? $pd->definition['fields'] : null;
			if ($fields) {
				$potentials = !empty($potentiallyAffectedLookup[$pdKey]) ? $potentiallyAffectedLookup[$pdKey] : [];
				foreach ($fields as $field) {
					if (in_array($field['name'], $potentials)) {
						if ($field['definition']['class'] == "neon\\core\\form\\fields\\Wysiwyg") {
							$wysiwygFields[$pdKey] = !empty($wysiwygFields[$pdKey]) ? $wysiwygFields[$pdKey] : [];
							$wysiwygFields[$pdKey][] = $field['name'];
						}
					}
				}
			}
		}

		$dds = neon('dds')->IDdsObjectManagement;
		foreach ($wysiwygFields as $classType => $fields) {
			$start=0;
			do {
				$found = $dds->listObjects($classType, $total, true, true, $start, 100);
				$start += count($found);
				foreach ($found as $objectData) {
					$changes = [];
					foreach ($fields as $field) {
						if (!empty($objectData[$field]) && strpos($objectData[$field],$search)!==false)
							$changes[$field] = str_replace($search, $replace, $objectData[$field]);
					}
					if (count($changes)) {
						$dds->editObject($objectData['_uuid'], $changes);
					}
				}
			} while (count($found)>0);
		}

		return true;
	}

}

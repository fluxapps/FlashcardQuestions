<?php

namespace srag\Plugins\FlashcardQuestions\Glossary;

use ilObjGlossary;
use srag\DIC\DICTrait;

require_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";
require_once "Services/Taxonomy/classes/class.ilTaxonomyTree.php";
require_once "Services/Taxonomy/classes/class.ilTaxonomyNode.php";
require_once __DIR__ . '/class.gl2tstModule.php';
require_once __DIR__ . '/class.gl2tstTopic.php';
require_once __DIR__ . '/class.gl2tstSection.php';

/**
 * Class gl2tstGlossary
 *
 * Proxy Object for ilObjGlossary
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class gl2tstGlossary {

	use DICTrait;
	/**
	 * @var ilObjGlossary
	 */
	protected $glossary;
	/**
	 * @var array gl2tstModule[]
	 */
	protected $modules;
	/**
	 * @var array gl2tstModule[]
	 */
	protected $sections;
	/**
	 * @var array gl2tstTopic[]
	 */
	protected $topics;


	/**
	 * @param ilObjGlossary $glossary
	 */
	public function __construct(ilObjGlossary $glossary) {
		$this->glossary = $glossary;
	}


	public function __call($method, $args) {
		if (method_exists($this->glossary, $method)) {
			return call_user_func_array(array( $this->glossary, $method ), $args);
		}
	}


	/**
	 * @return array ilTaxonomyNode[]
	 */
	public function getModules() {
		if ($this->modules === NULL) {
			$this->loadModulesAndTopicsAndSections();
		}

		return $this->modules;
	}


	/**
	 * @param $id
	 *
	 * @return null
	 */
	public function getModule($id) {
		foreach ($this->getModules() as $module) {
			if ($module->getId() == $id) {
				return $module;
			}
		}

		return NULL;
	}


	/**
	 * @param $id
	 *
	 * @return null
	 */
	public function getTopic($id) {
		foreach ($this->getTopics() as $topic) {
			if ($topic->getId() == $id) {
				return $topic;
			}
		}

		return NULL;
	}


	/**
	 * @return array ilTaxonomyNode[]
	 */
	public function getTopics() {
		if ($this->topics === NULL) {
			$this->loadModulesAndTopicsAndSections();
		}

		return $this->topics;
	}


	/**
	 * @return array ilTaxonomyNode[]
	 */
	public function getSections() {
		if ($this->sections === NULL) {
			$this->loadModulesAndTopicsAndSections();
		}

		return $this->sections;
	}


	/**
	 * @param $id
	 *
	 * @return null
	 */
	public function getSection($id) {
		foreach ($this->getSections() as $section) {
			if ($section->getId() == $id) {
				return $section;
			}
		}

		return NULL;
	}


	/**
	 * Return the profession (category title of the category where the glossary object lives in the tree)
	 *
	 * @return string
	 */
	public function getProfessionTitle() {
		$parent = self::dic()->tree()->getParentNodeData($this->glossary->getRefId());

		return $parent['title'];
	}


	/**
	 * Load modules and topics (lists of ilTaxonomyNode objects) of this glossary
	 */
	protected function loadModulesAndTopicsAndSections() {
		$tax_id = $this->glossary->getTaxonomyId();
		$taxonomy = new ilObjTaxonomy($tax_id);
		$tree = new ilTaxonomyTree($tax_id);
		$modules = array();
		$topics = array();
		$sections = array();
		foreach ($tree->getChilds($taxonomy->getTree()->readRootId()) as $child) {
			foreach ($tree->getChilds($child['child']) as $node) {
				if ($child['title'] == 'Themen') {
					$topics[] = new gl2tstTopic(new ilTaxonomyNode($node['obj_id']));
				} elseif ($child['title'] == 'Module') {
					$modules[] = new gl2tstModule(new ilTaxonomyNode($node['obj_id']));
				} elseif ($child['title'] == 'Abschnitt') {
					$sections[] = new gl2tstSection(new ilTaxonomyNode($node['obj_id']));
				}
			}
		}
		$this->modules = $modules;
		$this->topics = $topics;
		$this->sections = $sections;
	}


	/**
	 * Return a list of all glossaries containing test questions/answers
	 *
	 * Key   = ref_id of Glossary object
	 * Value = Title of profession
	 *
	 * @return array
	 */
	public static function getAllGlossaries() {
		$sql = "SELECT object_reference.ref_id, category_data.title FROM object_data AS glossary_data "
			. "INNER JOIN object_reference ON (object_reference.obj_id = glossary_data.obj_id) "
			. "INNER JOIN tree ON (object_reference.ref_id = tree.child) "
			. "INNER JOIN object_reference AS category_ref ON (tree.parent = category_ref.ref_id) "
			. "INNER JOIN object_data AS category_data ON (category_data.obj_id = category_ref.obj_id) " . "WHERE " . "glossary_data.type = 'glo' "
			. "AND glossary_data.title LIKE '%Offizielle Prüfungsfragen%' " . "AND category_data.type = 'cat' "
			. "AND object_reference.deleted IS NULL " . "AND category_ref.deleted IS NULL";
		$set = self::dic()->database()->query($sql);
		$return = array();
		while ($row = self::dic()->database()->fetchObject($set)) {
			$return[$row->ref_id] = $row->title;
		}

		return $return;
	}
}

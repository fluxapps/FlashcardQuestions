<?php

namespace srag\Plugins\FlashcardQuestions\GlossaryMigration;

use gl2tstTest;
use ilFlashcardQuestionsPlugin;
use ilGlossaryDefinition;
use ilObjCategory;
use ilObject;
use ilObjFlashcardQuestions;
use ilObjGlossary;
use ilObjTaxonomy;
use ilTaxNodeAssignment;
use srag\DIC\FlashcardQuestions\DICTrait;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestion;

/**
 * Class GlossaryMigrationWKV
 *
 * @package srag\Plugins\FlashcardQuestions\GlossaryMigration
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class GlossaryMigrationWKV {

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;

    /**
     * GlossaryMigration constructor.
     */
    public function __construct() {
    }

    /**
     * @param $pattern
     * @throws \ilTaxonomyException
     */
    public function run($pattern) {
        $mapping_ref_ids = array();
        $mapping_term_ids = array();
        $glossaries = $this->fetchGlossaries($pattern);
        foreach ($glossaries as $glossary) {
            $parent_id = self::dic()->tree()->getParentId($glossary->getRefId());
            $ilObjFlashcardQuestions = new ilObjFlashcardQuestions();
            $ilObjFlashcardQuestions->setTitle($glossary->getTitle());
            $ilObjFlashcardQuestions->setDescription($glossary->getDescription());
            $ilObjFlashcardQuestions->create();
            $ilObjFlashcardQuestions->createReference();
            $ilObjFlashcardQuestions->putInTree($parent_id);
            $ilObjFlashcardQuestions->setPermissions($parent_id);
            $ilObjFlashcardQuestions->setOnline($glossary->getOnline());

            $old_taxonomy = new ilObjTaxonomy($glossary->getTaxonomyId());
            $new_taxonomies = [];

            foreach (['Module', 'Themen', 'Abschnitt'] as $node_title) {
                if (!$node_id = $this->getNodeIdForTitle($node_title, $old_taxonomy)) {
                    continue;
                }

                $new_taxonomy = new ilObjTaxonomy();
                $new_taxonomy->setTitle($node_title);
                $new_taxonomy->create();
                ilObjTaxonomy::saveUsage($new_taxonomy->getId(), $ilObjFlashcardQuestions->getId());

                $old_taxonomy->cloneNodes($new_taxonomy, $this->getRootNode($new_taxonomy), $node_id);

                $new_taxonomy->setSortingMode($old_taxonomy->getSortingMode());
                $new_taxonomy->setItemSorting($old_taxonomy->getItemSorting());
                $new_taxonomy->update();

                $new_taxonomies[$new_taxonomy->getId()] = $new_taxonomy;
                if ($node_title == 'Module') {
                    $ilObjFlashcardQuestions->setReportLvl1($new_taxonomy->getId());
                } elseif ($node_title == 'Themen') {
                    $ilObjFlashcardQuestions->setReportLvl2($new_taxonomy->getId());
                }
            }

            $ilObjFlashcardQuestions->update();

            $node_mapping = $old_taxonomy->getNodeMapping();

            $terms = $glossary->getTermList();
            foreach ($terms as $term) {
                $definitions = ilGlossaryDefinition::getDefinitionList($term['id']);
                if (count($definitions) < 2) {
                    continue;
                }
                /** @var ilGlossaryDefinition $question_definition */
                /** @var ilGlossaryDefinition $answer_definition */
                $question_definition = $definitions[0];
                $answer_definition = $definitions[1];

                $xfcqQuestion = new xfcqQuestion();
                $xfcqQuestion->setActive(true);
                $xfcqQuestion->setObjId($ilObjFlashcardQuestions->getId());
                $xfcqQuestion->setOriginGloId($glossary->getId());
                $xfcqQuestion->setOriginTermId($term['id']);

                $new_question_id = $xfcqQuestion->getNextFreePageId();
                $this->migratePageObject($question_definition['id'], $new_question_id, $ilObjFlashcardQuestions->getId());
                $xfcqQuestion->setPageIdQuestion($new_question_id);

                $new_answer_id = $xfcqQuestion->getNextFreePageId();
                $this->migratePageObject($answer_definition['id'], $new_answer_id, $ilObjFlashcardQuestions->getId());
                $xfcqQuestion->setPageIdAnswer($new_answer_id);


                $new_tax_nodes = [];
                foreach ($this->getTaxNodeIds($glossary, $term) as $old_node_id) {
                    $new_node_id = $node_mapping[$old_node_id];
                    foreach ($new_taxonomies as $key => $new_tax) {
                        /** @var $new_tax ilObjTaxonomy */
                        if ($new_tax->getTree()->isInTree($new_node_id)) {
                            $new_tax_nodes[$key][] = $new_node_id;
                        }
                    }
                }

                $xfcqQuestion->setTaxNodes($new_tax_nodes);
                $xfcqQuestion->create(true);

                $this->migrateFlashCards($term['id'], $xfcqQuestion->getId());
                $mapping_term_ids[$term['id']] = $xfcqQuestion->getId();
            }

            $this->migrateFlashCardObjects($glossary->getRefId(), $ilObjFlashcardQuestions->getRefId());
            $this->migrateGl2Tests($glossary->getRefId(), $ilObjFlashcardQuestions->getRefId(), $node_mapping);

            $mapping_ref_ids[$glossary->getRefId()] = $ilObjFlashcardQuestions->getRefId();

	        // Read the Parent Title and RefId
	        // Add the Perent Title and RefId to Glossary Title
	        // Move the Glossary to the Repository Root for manual archiving
	        $parent_ref_id = self::dic()->tree()->getParentId($glossary->getRefId());
	        if(ilObject::_lookupType($parent_ref_id, true) == "cat") {
		        $parent_object = new ilObjCategory($parent_ref_id);

		        $glossary->setTitle($parent_object->getTitle()." / ".$parent_object->getRefId()." | ".$glossary->getTitle());
		        $glossary->update();
		        // $parent_object
		        self::dic()->tree()->moveTree($glossary->getRefId(),1);
	        }


        }

        return $mapping_ref_ids;
    }

    /**
     * @param int $glo_ref_id
     * @param int $xfcq_ref_id
     * @return array
     */
    protected function migrateFlashCardObjects($glo_ref_id, $xfcq_ref_id) {
        $migrated_obj_ids = array();
        $query = self::dic()->database()->query('SELECT obj_id, glossary_ref_id, card_pool_type, xfcq_ref_id FROM rep_robj_xflc_data where glossary_ref_id = ' . $glo_ref_id);
        while ($set = self::dic()->database()->fetchAssoc($query)) {
            if (($set['card_pool_type'] == 0) && ($set['xfcq_ref_id'] == 0)) {
                self::dic()->database()->query(
                    'UPDATE rep_robj_xflc_data 
                            SET card_pool_type = 1, glossary_ref_id = 0, xfcq_ref_id = ' . $xfcq_ref_id . ' 
                            WHERE obj_id = ' . $set['obj_id']);
				$migrated_obj_ids[] = $set['obj_id'];
			}
		}

		return $migrated_obj_ids;
	}


	/**
	 * @param int $term_id
	 * @param int $xfcq_qst_id
	 */
	protected function migrateFlashCards($term_id, $xfcq_qst_id) {
		static $migrated_card_ids;
		if (!is_array($migrated_card_ids)) {
		    $migrated_card_ids = array();
        }

	    $query = self::dic()->database()->query('SELECT * FROM rep_robj_xflc_cards WHERE term_id = ' . $term_id);
		while ($set = self::dic()->database()->fetchAssoc($query)) {
		    $card_id = $set['card_id'];
		    if (!in_array($card_id, $migrated_card_ids)) {
                self::dic()->database()->query('UPDATE rep_robj_xflc_cards 
                        SET term_id = ' . $xfcq_qst_id . ' 
                        WHERE card_id = ' . $card_id);
                $migrated_card_ids[] = $card_id;
            }
        }
	}


    /**
     * @param $pattern
     * @return ilObjGlossary[]
     */
	protected function fetchGlossaries($pattern) {
		$query = self::dic()->database()->query('SELECT ref_id from object_data d 
                    inner join object_reference r on d.obj_id = r.obj_id 
                    where type = "glo" 
                    and d.title LIKE ' . self::dic()->database()->quote($pattern, 'text') . '
                    and deleted is null'
        );
        $glossaries = [];
        while ($set = self::dic()->database()->fetchAssoc($query)) {
            $glossaries[] = new ilObjGlossary($set['ref_id']);
        }
        return $glossaries;
    }

    /**
     * @param int $old_page_id
     * @param int $new_parent_id
     */
    protected function migratePageObject($old_page_id, $new_page_id, $new_parent_id) {
        self::dic()->database()->query(
            'INSERT INTO page_object (page_id, parent_id, content, parent_type, last_change_user, view_cnt, last_change, created, create_user, render_md5, rendered_content, rendered_time, activation_start, activation_end, active, is_empty, inactive_elements, int_links, show_activation_info, lang, edit_lock_user, edit_lock_ts)'
            . ' select ' . $new_page_id . ', ' . $new_parent_id . ', content, "xfcq", last_change_user, view_cnt, last_change, created, create_user, render_md5, rendered_content, rendered_time, activation_start, activation_end, active, is_empty, inactive_elements, int_links, show_activation_info, lang, edit_lock_user, edit_lock_ts'
            . ' from page_object'
            . ' where parent_type = "gdf" and page_id = ' . $old_page_id
        );
        
    }

    /**
     * @param ilObjGlossary $glossary
     * @param array $term
     * @return array
     * @throws \ilTaxonomyException
     */
    protected function getTaxNodeIds(ilObjGlossary $glossary, array $term) {
        $ta = new ilTaxNodeAssignment("glo", $glossary->getId(), "term", $glossary->getTaxonomyId());
        $assgnmts = $ta->getAssignmentsOfItem($term['id']);
        $node_ids = array();
        foreach ($assgnmts as $a) {
            $node_ids[] = $a["node_id"];
        }
        return $node_ids;
    }

    /**
     * @param $title
     * @param ilObjTaxonomy $taxonomy
     * @return int
     */
    protected function getNodeIdForTitle($title, ilObjTaxonomy $taxonomy) {
        $root_node = $this->getRootNode($taxonomy);
        foreach ($taxonomy->getTree()->getChilds($root_node) as $child) {
            if ($child['title'] == $title) {
                return $child['child'];
            }
        }
        return 0;
    }

    /**
     * @param ilObjTaxonomy $taxonomy
     * @return mixed
     */
    protected function getRootNode(ilObjTaxonomy $taxonomy) {
        $query = self::dic()->database()->query('SELECT child FROM tax_tree WHERE parent = 0 AND tax_tree_id = ' . $taxonomy->getTree()->getTreeId());
        return self::dic()->database()->fetchAssoc($query)['child'];
    }

    /**
     * @param $glo_ref_id
     * @param $xfcq_ref_id
     * @param $node_mapping
     */
    protected function migrateGl2Tests($glo_ref_id, $xfcq_ref_id, $node_mapping) {
        /** @var gl2tstTest $gl2tstTest */
        require_once 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Glossary2Test/classes/Test/class.gl2tstTest.php';
        foreach (gl2tstTest::where(array('glossary_ref_id' => $glo_ref_id))->get() as $gl2tstTest) {
            $new_content = array();
            foreach ($gl2tstTest->getContent() as $old_content) {
                if ($module = $old_content['module']) {
                    $old_content['module'] = $node_mapping[$old_content['module']];
                }
                if ($module = $old_content['topic']) {
                    $old_content['topic'] = $node_mapping[$old_content['topic']];
                }
                if ($module = $old_content['section']) {
                    $old_content['section'] = $node_mapping[$old_content['section']];
                }
                $new_content[] = $old_content;
            }
            $gl2tstTest->setContent($new_content);
            $gl2tstTest->setGlossaryRefId($xfcq_ref_id);
            $gl2tstTest->update();
        }
    }
}
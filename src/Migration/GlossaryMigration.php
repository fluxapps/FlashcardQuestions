<?php
namespace srag\Plugins\FlashcardQuestions\GlossaryMigration;

use srag\DIC\DICTrait;
use \ilFlashcardQuestionsPlugin;
use \ilObjGlossary;
use \ilGlossaryDefinition;
use \ilObjFlashcardQuestions;
use \ilObjTaxonomy;
use  srag\Plugins\FlashcardQuestions\Question\xfcqQuestion;
use \ilGlossaryTerm;
use \ilTaxNodeAssignment;
/**
 * Class GlossaryMigration
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class GlossaryMigration {

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;

    /**
     * GlossaryMigration constructor.
     */
    public function __construct() {
    }

    /**
     *
     */
    public function run() {
        $glossaries = $this->fetchGlossaries();
        foreach ($glossaries as $glossary) {
            if ($glossary->getRefId() != 3957) {
                continue;
            }
            $parent_id = self::dic()->tree()->getParentId($glossary->getRefId());
            $ilObjFlashcardQuestions = new ilObjFlashcardQuestions();
            $ilObjFlashcardQuestions->setTitle($glossary->getTitle());
            $ilObjFlashcardQuestions->setDescription($glossary->getDescription());
            $ilObjFlashcardQuestions->create();
            $ilObjFlashcardQuestions->createReference();
            $ilObjFlashcardQuestions->putInTree($parent_id);
            $ilObjFlashcardQuestions->setPermissions($parent_id);
            $ilObjFlashcardQuestions->setOnline($glossary->getOnline());
            $ilObjFlashcardQuestions->update();

            $old_taxonomy = new ilObjTaxonomy($glossary->getTaxonomyId());
            $new_taxonomy = new ilObjTaxonomy($ilObjFlashcardQuestions->getTaxonomyId());
            $old_taxonomy->cloneNodes($new_taxonomy, $old_taxonomy->getTree()->getRootId(), $new_taxonomy->getTree()->getRootId());
            $new_taxonomy->setSortingMode($old_taxonomy->getSortingMode());
            $new_taxonomy->setItemSorting($old_taxonomy->getItemSorting());
            $new_taxonomy->update();

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


                self::dic()->log()->write('term_id: ' . $term['id']);
                self::dic()->log()->write('page_id: ' . $question_definition['id']);
                self::dic()->log()->write('page_id: ' . $answer_definition['id']);
                $this->migratePageObject($question_definition['id'], $ilObjFlashcardQuestions->getId());
                $this->migratePageObject($answer_definition['id'], $ilObjFlashcardQuestions->getId());

                $xfcqQuestion = new xfcqQuestion();
                $xfcqQuestion->setTitle($term['term']);
                $xfcqQuestion->setActive(true);

                $xfcqQuestion->setTaxNodes($this->getTaxNodeIds($glossary, $term));
                $xfcqQuestion->setObjId($ilObjFlashcardQuestions->getId());
                $xfcqQuestion->setPageIdQuestion($question_definition['id']);
                $xfcqQuestion->setPageIdAnswer($answer_definition['id']);
                $xfcqQuestion->create(true);
            }

        }
    }

    /**
     * @return ilObjGlossary[]
     */
    protected function fetchGlossaries(): array {
        $query = self::dic()->database()->query('SELECT ref_id from object_data d inner join object_reference r on d.obj_id = r.obj_id where type = "glo" and deleted is null');
        $glossaries = [];
        while ($set = self::dic()->database()->fetchAssoc($query)) {
            $glossaries[] = new ilObjGlossary($set['ref_id']);
        }
        return $glossaries;
    }

    /**
     * @param int $page_id
     * @param int $new_parent_id
     */
    protected function migratePageObject(int $page_id, int $new_parent_id) {
        self::dic()->database()->query(
            'INSERT INTO page_object (page_id, parent_id, content, parent_type, last_change_user, view_cnt, last_change, created, create_user, render_md5, rendered_content, rendered_time, activation_start, activation_end, active, is_empty, inactive_elements, int_links, show_activation_info, lang, edit_lock_user, edit_lock_ts)'
            . ' select page_id, ' . $new_parent_id . ', content, "xfcq", last_change_user, view_cnt, last_change, created, create_user, render_md5, rendered_content, rendered_time, activation_start, activation_end, active, is_empty, inactive_elements, int_links, show_activation_info, lang, edit_lock_user, edit_lock_ts'
            . ' from page_object'
            . ' where parent_type = "gdf" and page_id = ' . $page_id
        );
        
    }

    /**
     * @param ilObjGlossary $glossary
     * @param array $term
     * @return array
     * @throws \ilTaxonomyException
     */
    protected function getTaxNodeIds(ilObjGlossary $glossary, array $term): array {
        $ta = new ilTaxNodeAssignment("glo", $glossary->getId(), "term", $glossary->getTaxonomyId());
        $assgnmts = $ta->getAssignmentsOfItem($term['id']);
        $node_ids = array();
        foreach ($assgnmts as $a) {
            $node_ids[] = $a["node_id"];
        }
        return $node_ids;
    }
}
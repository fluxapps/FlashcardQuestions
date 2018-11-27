<?php

namespace srag\Plugins\FlashcardQuestions\Glossary;

use ilGlossaryTerm;
use srag\DIC\DICTrait;

require_once "Modules/Glossary/classes/class.ilGlossaryDefinition.php";

/**
 * Class gl2tstTerm
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class gl2tstTerm {

	use DICTrait;
	/**
	 * @var ilGlossaryTerm
	 */
	protected $term;
	/**
	 * @var gl2tstGlossary
	 */
	protected $glossary;
	/**
	 * @var string
	 */
	protected $question = '';
	/**
	 * @var string
	 */
	protected $answer = '';


	/**
	 * @param ilGlossaryTerm $term
	 */
	public function __construct(ilGlossaryTerm $term) {
		$this->term = $term;
	}


	public function __call($method, $args) {
		if (method_exists($this->term, $method)) {
			return call_user_func_array(array( $this->term, $method ), $args);
		}
	}


	/**
	 * Return the unique term ID from the Advanced Meta Data
	 *
	 * @return mixed
	 */
	public function getTermID() {
		$set = self::dic()->database()->query("SELECT value FROM adv_md_values_text WHERE field_id = 1 AND sub_type = 'term' AND obj_id = "
			. $ilDB->quote($this->getGlossary()->getId(), 'integer') . " AND sub_id = " . $ilDB->quote($this->term->getId(), 'integer'));

		return self::dic()->database()->fetchObject($set)->value;
	}


	/**
	 * Return the section (Abschnitt)
	 */
	public function getSection() {
		$set = self::dic()->database()->query("SELECT value FROM adv_md_values_text WHERE field_id = 5 AND sub_type = 'term' AND obj_id = "
			. self::dic()->database()->quote($this->getGlossary()->getId(), 'integer') . " AND sub_id = " . self::dic()->database()
				->quote($this->term->getId(), 'integer'));

		return self::dic()->database()->fetchObject($set)->value;
	}


	/**
	 * @return string
	 */
	public function getQuestion() {
		$this->loadQuestionAndAnswer();

		return $this->question;
	}


	/**
	 * @return string
	 */
	public function getAnswer() {
		$this->loadQuestionAndAnswer();

		return $this->answer;
	}


	protected function loadQuestionAndAnswer() {
		$definitions = ilGlossaryDefinition::getDefinitionList($this->term->getId());
		foreach ($definitions as $k => $definition_data) {
			$definition = new ilGlossaryDefinition($definition_data['id']);
			/** @var ilGlossaryDefPage $page */
			$page = $definition->getPageObject();
			$page->buildDom();
			if ($k == 0) {
				$this->question = $page->getFirstParagraphText();
			} else {
				$this->answer = $page->getFirstParagraphText();
			}
		}
	}


	/**
	 * @return gl2tstGlossary
	 */
	public function getGlossary() {
		if ($this->glossary === NULL) {
			$this->glossary = new gl2tstGlossary(new ilObjGlossary($this->term->getGlossaryId(), false));
		}

		return $this->glossary;
	}
}

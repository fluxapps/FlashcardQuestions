<?php

namespace srag\Plugins\FlashcardQuestions\Glossary;

/**
 * Class gl2tstSection
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class gl2tstSection {

	/**
	 * @var ilTaxonomyNode
	 */
	protected $node;


	/**
	 * @param ilTaxonomyNode $node
	 */
	public function __construct(ilTaxonomyNode $node) {
		$this->node = $node;
	}


	public function __call($method, $args) {
		if (method_exists($this->node, $method)) {
			return call_user_func_array(array( $this->node, $method ), $args);
		}
	}
}
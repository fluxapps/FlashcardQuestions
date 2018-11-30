<?php

namespace srag\Plugins\FlashcardQuestions\Glossary;

use ilTaxonomyNode;

/**
 * Class gl2tstTopic
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class gl2tstTopic {

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
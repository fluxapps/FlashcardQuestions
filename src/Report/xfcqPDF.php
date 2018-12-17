<?php
namespace srag\Plugins\FlashcardQuestions\Report;
/**
 * Interface xfcqPDF
 */
interface xfcqPDF {

    /**
     * @param $path
     * @return bool
     */
    public function save($path);


    /**
     * @param $filename
     */
    public function download($filename);
    
}

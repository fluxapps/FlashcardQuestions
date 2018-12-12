<?php
namespace srag\Plugins\FlashcardQuestions\Report;
use ilFlashcardQuestionsPlugin;
use ilObjFile;
use ilObjFlashcardQuestions;
use ilTaxonomyNode;
use \Mpdf\Mpdf;
use srag\DIC\FlashcardQuestions\DICTrait;
use srag\Plugins\FlashcardQuestions\Config\Config;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class xfcqMPDF implements xfcqPDF
{

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;

    /**
     * @var mPDF
     */
    protected $mpdf;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var glorepGlossary
     */
    protected $flashcard_questions;

    /**
     * @var bool
     */
    protected $printID = true;

    /**
     * @var bool
     */
    protected $printAnswers = true;

    /**
     * @var int
     */
    protected $current_number = 1;

    /**
     * @var int|null
     */
    protected $lvl_1;

    /**
     * @var int|null
     */
    protected $lvl_2;

    /**
     * @param ilObjFlashcardQuestions $flashcard_questions
     * @param array $data
     * @throws \Mpdf\MpdfException
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    public function __construct($flashcard_questions, $data)
    {
        $this->data = $data;
        $this->flashcard_questions = $flashcard_questions;

        $this->lvl_1 = $this->flashcard_questions->getReportLvl1();
        $this->lvl_2 = $this->flashcard_questions->getReportLvl2();
        if (!$this->lvl_1 && $this->lvl_2) {
            $this->lvl_1 = $this->lvl_2;
            $this->lvl_2 = null;
        }

        $this->structureData();
        $this->mpdf = new Mpdf();
        // Add global styles to style the reports HTML
        $this->mpdf->WriteHTML(file_get_contents(self::plugin()->directory() . '/templates/css/report.css'), 1);
        $this->setPageHeader();
        $this->setPageFooter();
    }


    /**
     * @throws \ilTemplateException
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    protected function setPageHeader()
    {
        $tpl = self::plugin()->template('reports/tpl.pdf_header.html', false, false);
        $this->mpdf->SetHTMLHeader($tpl->get());
    }


    /**
     * @throws \ilTemplateException
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    protected function setPageFooter()
    {
        $tpl = self::plugin()->template('reports/tpl.pdf_footer.html', false, false);
        $tpl->setVariable('DATE', strftime('%e.%d.%Y'));
        $this->mpdf->SetHTMLFooter($tpl->get());
    }


    /**
     * @throws \ilTemplateException
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    protected function writeFirstPage()
    {
        $tpl = self::plugin()->template('reports/tpl.pdf_frontpage.html');
        $file_id = Config::getField(Config::C_REPORT_LOGO);
        $tpl->setVariable('LOGO_SRC', ilObjFile::_lookupAbsolutePath($file_id));
        $tpl->setVariable('REPORT_TITLE', $this->flashcard_questions->getProfessionTitle());
        $tpl->setVariable('REPORT_SUBTITLE', $this->flashcard_questions->getTitle());
        $tpl->setVariable('CONTENT_OVERVIEW', $this->renderContentOverview());
        $this->html($tpl->get());
        $this->mpdf->TOCpagebreakByArray(array(
            'TOCuseLinking' => true,
        ));
    }


    /**
     * @throws \ilTemplateException
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    public function parse()
    {
        $this->writeFirstPage();
        $first = true;
        foreach ($this->data as $lvl_1_key => $lvl_2) {
            if (!$first) {
                $this->pageBreak();
            }
            $first = false;

            $this->mpdf->TOC_Entry(ilTaxonomyNode::_lookupTitle($lvl_1_key), 0);
            $this->html("<h1>" . ilTaxonomyNode::_lookupTitle($lvl_1_key) . "</h1>");

            if (!$this->lvl_2) {
                $tpl = self::plugin()->template('reports/tpl.pdf_question_answer.html');
                $tpl->setVariable('NUMBER', sprintf('%04d', $this->current_number++));
                $tpl->setVariable('ID', ($this->isPrintID()) ? $lvl_2['id'] : '&nbsp;');
                $tpl->setVariable('QUESTION', "{$lvl_2['question']}");
                if ($this->isPrintAnswers()) {
                    $tpl->setVariable('ANSWER', "{$lvl_2['answer']}");
                }
                $this->html($tpl->get());
                continue;
            }

            foreach ($lvl_2 as $lvl_2_key => $data) {
                $this->mpdf->TOC_Entry(ilTaxonomyNode::_lookupTitle($lvl_2_key), 1);
                $this->html("<h2>Thema: " . ilTaxonomyNode::_lookupTitle($lvl_2_key) . "</h2>");

                $tpl = self::plugin()->template('reports/tpl.pdf_question_answer.html');
                $tpl->setVariable('NUMBER', sprintf('%04d', $this->current_number++));
                $tpl->setVariable('ID', ($this->isPrintID()) ? $data['id'] : '&nbsp;');
                $tpl->setVariable('QUESTION', "{$data['question']}");
                if ($this->isPrintAnswers()) {
                    $tpl->setVariable('ANSWER', "{$data['answer']}");
                }
                $this->html($tpl->get());
            }
        }
//
//        $group_module = $this->data[0]['module'];
//        $group_topic = $this->data[0]['topic'];
//        $group_section = $this->data[0]['section'];
//        foreach ($this->data as $i => $data) {
//            $module_changed = ($data['module'] != $group_module);
//            $topic_changed = ($data['topic'] != $group_topic);
//            $section_changed = ($data['section'] != $group_section);
//            if ($module_changed || $i == 0) {
//                // If a new module starts, always insert a page break, except for the first
//                if ($i != 0) {
//                    $this->pageBreak();
//                }
//                if ($data['module']) {
//                    $this->mpdf->TOC_Entry($data['module'], 0);
//                    $this->html("<h1>" . $data['module'] . "</h1>");
//                }
//            }
//            if ($topic_changed || $i == 0 || ($module_changed && !$topic_changed)) {
//                if ($data['topic']) {
//                    $this->mpdf->TOC_Entry($data['topic'], 1);
//                    $this->html("<h2>Thema: " . $data['topic'] . "</h2>");
//                }
//            }
//            if ($section_changed || $i == 0 || (($module_changed || $topic_changed) && !$section_changed)) {
//                if ($data['section']) {
//                    $this->mpdf->TOC_Entry($data['section'], 2);
//                    $this->html("<h3>" . $data['section'] . "</h3>");
//                }
//            }
//            $tpl = self::plugin()->template('reports/tpl.pdf_question_answer.html');
//            $tpl->setVariable('NUMBER', sprintf('%04d', $data['number']));
//            $tpl->setVariable('ID', ($this->isPrintID()) ? $data['id'] : '&nbsp;');
//            $tpl->setVariable('QUESTION', "{$data['question']}");
//            if ($this->isPrintAnswers()) {
//                $tpl->setVariable('ANSWER', "{$data['answer']}");
//            }
//            $this->html($tpl->get());
//            $group_module = $data['module'];
//            $group_topic = $data['topic'];
//            $group_section = $data['section'];
//        }
    }


    /**
     * Write HTML to mPDF
     *
     * @param string $html
     * @return $this
     * @throws MpdfException
     */
    public function html($html)
    {
        $this->mpdf->WriteHTML($html, 2);

        return $this;
    }


    /**
     * Insert a page break at current position in PDF
     *
     * @return $this
     */
    public function pageBreak()
    {
        $this->html('<pagebreak>');

        return $this;
    }


    /**
     * @param $path
     * @return mixed
     */
    public function save($path)
    {
        $this->mpdf->Output($path, 'F');
    }


    /**
     * @param $filename
     * @throws \Mpdf\MpdfException
     */
    public function download($filename)
    {
        $this->mpdf->Output($filename, 'D');
    }


    /**
     * @return boolean
     */
    public function isPrintID()
    {
        return $this->printID;
    }


    /**
     * @param boolean $printID
     */
    public function printID($printID)
    {
        $this->printID = $printID;
    }


    /**
     * @return boolean
     */
    public function isPrintAnswers()
    {
        return $this->printAnswers;
    }


    /**
     * @param boolean $printAnswers
     */
    public function printAnswers($printAnswers)
    {
        $this->printAnswers = $printAnswers;
    }


    /**
     * Renders a list of all available modules and topics for the first page of the PDF report
     */
    protected function renderContentOverview()
    {
        $out = '';

        foreach ($this->data as $lvl_1_key => $data) {
            $out .= ilTaxonomyNode::_lookupTitle($lvl_1_key);
            $out .= '<ul>';

            foreach ($data as $lvl_2_key => $set) {
                $out .= '<li>';
                $out .= ilTaxonomyNode::_lookupTitle($lvl_2_key);
                $out .= '</li>';
            }

            $out .= '</ul>';
        }

        return $out;
    }

    /**
     *  build this structure:
     *  array(
     *      tax_node_lvl1_x =>
     *          $tax_node_lvl2_x => $records with these nodes
     *          $tax_node_lvl2_y => $records with these nodes
     *          ...
     *      $tax_node_lvl2_y =>
     *          $tax_node_lvl2_x => $records with these nodes
     *          $tax_node_lvl2_y => $records with these nodes
     *          ...
     *      ...
     */
    protected function structureData() {
        if (!$this->lvl_1) {
            return;
        }

        $structured_data = array();

        foreach ($this->data as $set) {
            if(!is_array($set['tax_nodes'])) {
                $set['tax_nodes'] = array();
            }
            if (!is_array($set['tax_nodes'][$this->lvl_1]) || empty($set['tax_nodes'][$this->lvl_1])) {
                $set['tax_nodes'][$this->lvl_1] = array(0);
            }
            if (!is_array($set['tax_nodes'][$this->lvl_2]) || empty($set['tax_nodes'][$this->lvl_2])) {
                $set['tax_nodes'][$this->lvl_2] = array(0);
            }

            foreach ($set['tax_nodes'][$this->lvl_1] as $node_1) {
                if (!$this->lvl_2) {
                    $structured_data[$node_1] = $set;
                    continue;
                }
                foreach ($set['tax_nodes'][$this->lvl_2] as $node_2) {
                    if (!is_array($structured_data[$node_1])) {
                        $structured_data[$node_1] = array();
                    }
                    $structured_data[$node_1][$node_2] = $set;
                }
            }
        }

        $this->data = $structured_data;
    }

}
<?php

namespace EADImport\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Stdlib\Message;
use Omeka\Service\Exception\ConfigException;
use EADImport\Validator\XmlValidator;
use EADImport\NodeXpaths\EAD2002Xpath;
use EADImport\Form\LoadForm;
use EADImport\Form\MappingForm;

class IndexController extends AbstractActionController
{
    /**
     * @var string
     */
    protected $tempDir;

    protected $tempPath;

    public function __construct($tempDir)
    {
        $this->tempDir = $tempDir;
    }

    public function loadAction()
    {
        $form = $this->getForm(LoadForm::class);

        $view = new ViewModel();
        $view->setVariable('form', $form);

        return $view;
    }

    public function mapAction()
    {
        $view = new ViewModel;
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('admin/eadimport');
        }

        $post = array_merge_recursive(
            $request->getPost()->toArray(),
            $request->getFiles()->toArray()
        );
        $this->moveToTemp($post['source']['tmp_name']);
        $xmlFilePath = $this->getTempPath();

        if ($post['source']['type'] !== 'text/xml') {
            $this->messenger()->addError(sprintf("%s is not a xml file", $post['source']['name'])); // @translate
            return $this->redirect()->toRoute('admin/eadimport');
        }

        $xmlFileObject = $this->xmlToObject($xmlFilePath);
        $options['xml_schema_object'] = $xmlFileObject;

        $importForm = $this->getForm(MappingForm::class, $options);
        $importForm->setData($post);

        if (!$importForm->isValid()) {
            $this->messenger()->addFormErrors($importForm);
            return $this->redirect()->toRoute('admin/eadimport');
        }

        $importName = $post['import_name'];
        $siteId = $post['site_id'];
        $xmlSchema = $post['schema'];
        $xmlSchemaObject = $this->xmlToObject($xmlFilePath);
        $options['xml_file_object'] = $xmlSchemaObject;

        if ($xmlSchema !== 'None') {
            $xmlSchemaPath = OMEKA_PATH . '/modules/EADImport/data/schemas/' . $xmlSchema;

            $xmlValidator = new XmlValidator;
            $validated = $xmlValidator->validateFeeds($xmlFilePath, $xmlSchemaPath);
            if (! $validated) {
                $this->messenger()->addError(implode('/', $xmlValidator->displayErrors())); // @translate
                return $this->redirect()->toRoute('admin/eadimport');
            }
        }

        $nodeList = $this->getNodeList($xmlFilePath);

        $view->setVariable('form', $importForm);
        $view->setVariable('nodeList', $nodeList);
        $view->setVariable('xmlFilePath', $xmlFilePath);
        $view->setVariable('importName', $importName);
        $view->setVariable('siteId', $siteId);

        return $view;
    }

    public function importAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('admin/eadimport');
        }
        $post = $request->getPost()->toArray();
        unset($post['csrf']);
        $args = $post;

        $dispatcher = $this->jobDispatcher();
        $job = $dispatcher->dispatch('EADImport\Job\ImportJob', $args);

        $message = new Message(
            'Importing in background (%sjob #%d%s)', // @translate
                sprintf(
                    '<a href="%s">',
                    htmlspecialchars($this->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]))
                ),
            $job->getId(),
            '</a>'
        );
        $message->setEscapeHtml(false);
        $this->messenger()->addSuccess($message);
        return $this->redirect()->toRoute('admin/eadimport/past-imports');
    }

    public function pastImportsAction()
    {
        $view = new ViewModel;
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->params()->fromQuery() + [
            'page' => $page,
            'sort_by' => $this->params()->fromQuery('sort_by', 'id'),
            'sort_order' => $this->params()->fromQuery('sort_order', 'desc'),
        ];
        $response = $this->api()->search('eadimport_imports', $query);
        $this->paginator($response->getTotalResults(), $page);
        $view->setVariable('imports', $response->getContent());
        return $view;
    }

    protected function xmlToObject($xmlFilePath)
    {
        $xmlToString = file_get_contents($xmlFilePath);
        $xmlToObject = simplexml_load_string($xmlToString);

        return $xmlToObject;
    }

    protected function getNodeList($xmlFile)
    {
        $doc = new \DOMDocument();
        $doc->load($xmlFile);

        $domxpath = new \DOMXPath($doc);
        $subNodeMapping = new EAD2002Xpath;
        $subNodeMappingPaths = $subNodeMapping->getPaths();

        $list['nodes'] = $domxpath->query("//c[@level]");
        $list['nodes_paths'] = [];
        foreach ($list['nodes'] as $node) {
            $nodeLevel = $node->getAttribute('level');
            foreach ($subNodeMappingPaths as $subNodePath) {
                $subNodeList = $domxpath->query($subNodePath, $node);

                if (sizeof($subNodeList) > 0) {
                    $list['nodes_paths'][$nodeLevel][$subNodePath] = true;
                }
            }
        }
        foreach (array_keys($list['nodes_paths']) as $level) {
            $nodesByLevel = $domxpath->query("//c[@level='$level']");
            $list['nodes_counts'][$level] = $nodesByLevel->count();
        }
        return $list;
    }

    protected function getTempPath($tempDir = null)
    {
        if (isset($this->tempPath)) {
            return $this->tempPath;
        }
        if (!isset($tempDir)) {
            if (!isset($this->tempDir)) {
                throw new ConfigException('Missing temporary directory configuration');
            }
            $tempDir = $this->tempDir;
        }
        $this->tempPath = tempnam($tempDir, 'omeka');
        return $this->tempPath;
    }

    protected function moveToTemp($systemTempPath)
    {
        move_uploaded_file($systemTempPath, $this->getTempPath());
    }
}

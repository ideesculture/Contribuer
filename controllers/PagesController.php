<?php
    /* ----------------------------------------------------------------------
     * simpleListEditor
     * ----------------------------------------------------------------------
     * List & list values editor plugin for Providence - CollectiveAccess
     * Open-source collections management software
     * ----------------------------------------------------------------------
     *
     * Plugin by idéesculture (www.ideesculture.com)
     * This plugin is published under GPL v.3. Please do not remove this header
     * and add your credits thereafter.
     *
     * File modified by :
     * ----------------------------------------------------------------------
     */
    ini_set("display_errors", 1);
    error_reporting(E_ERROR);
    require_once(__CA_MODELS_DIR__.'/ca_lists.php');
    require_once(__CA_MODELS_DIR__.'/ca_objects.php');
    require_once(__CA_MODELS_DIR__.'/ca_site_pages.php');
    require_once(__CA_MODELS_DIR__.'/ca_entities.php');
    require_once(__CA_MODELS_DIR__.'/ca_places.php');
    
    require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
    require_once(__CA_MODELS_DIR__.'/ca_list_items.php');
    require_once(__CA_MODELS_DIR__.'/ca_object_labels.php');
    require_once(__CA_LIB_DIR__."/Search/EntitySearch.php");
    require_once(__CA_LIB_DIR__."/Search/CollectionSearch.php");
	error_reporting(E_ERROR);

 	class PagesController extends ActionController
    {
        # -------------------------------------------------------
        protected $opo_config;        // plugin configuration file
        private $plugin_path;
        # -------------------------------------------------------
        # Constructor
        # -------------------------------------------------------

        public function __construct(&$po_request, &$po_response, $pa_view_paths = null)
        {
            parent::__construct($po_request, $po_response, $pa_view_paths);

            $this->plugin_path = __CA_APP_DIR__ . '/plugins/Contribuer';

            if (is_file(__CA_THEME_DIR__.'/conf/contribuer.conf')) {
                $this->opo_config = Configuration::load(__CA_THEME_DIR__.'/conf/contribuer.conf');
            } else {
                $this->opo_config = Configuration::load(__CA_APP_DIR__.'/plugins/Contribuer/conf/contribuer.conf');
            }

            // Extracting theme name to properly handle views in distinct theme dirs
            $vs_theme_dir = explode("/", $po_request->getThemeDirectoryPath());
            $vs_theme = end($vs_theme_dir);
            $this->opa_view_paths[] = $this->plugin_path."/themes/".$vs_theme."/views";
        }

        # -------------------------------------------------------
        # Functions to render views
        # -------------------------------------------------------
        public function Form()
        {
            // Exiting if anonymous contributions are not allowed
            if (!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
                //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
                die("redirection...");
            }

            $id = $this->request->getParameter("id", pInteger);
            $table = "ca_site_pages";
            $this->view->setVar("table", $table);
            // No type but a template_id for website pages
            $template = $this->request->getParameter("template", pString);
            $this->view->setVar("template", $template);

            // TODO : go from "templage" to "template_id" with table ca_site_templates
            $this->view->setVar("template_id", 1);

            $parent_id = $this->request->getParameter("parent_id", pString);
            $this->view->setVar("parent_id", $parent_id);

            $this->view->setVar("template", $this->opo_config->get("template"));
            $mappings = $this->opo_config->get("form");

            // If we have parent_id, we need to override the template to disallow direct selection
            if ($parent_id) {
                foreach ($mappings[$table][$template] as $key => $mapping) {
                    $target = explode(".", $mapping["mapping"])[1];
                    if ($target == "parent_id") {
                        unset($mapping["dataSource"]);
                        $mapping["options"] = ["type" => "hidden"];
                        $mapping["default"] = $parent_id;
                        $mappings[$table][$template][$key] = $mapping;
                        break;
                    }
                }
            }
            $this->view->setVar("mappings", $mappings[$table][$template]);
            $mapping = $mappings[$table][$template];
            $label = "create a new article";
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('Pages/addform_html.php');
        }

        public function EditForm() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
                //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $id= $this->request->getParameter("id", pInteger);
            $this->view->setVar("id", $id);
            $table = "ca_site_pages";
            $this->view->setVar("table", $table);
            // No type but a template_id for website pages
            $template = $this->request->getParameter("template", pString);
            $this->view->setVar("template", $template);
            $parent_id = $this->request->getParameter("parent_id", pString);
            $this->view->setVar("parent_id", $parent_id);

            $id = $this->request->getParameter("id", pString);
            $this->view->setVar("id", $id);
            if(!$id) {
                //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $vt_page = new ca_site_pages($id);

            $this->view->setVar("template", $this->opo_config->get("template"));
            $mappings = $this->opo_config->get("form");


            // If we have parent_id, we need to override the template to disallow direct selection
            if($parent_id) {
                foreach($mappings[$table][$template] as $key=>$mapping) {
                    $target = explode(".", $mapping["mapping"])[1];
                    if($target == "parent_id") {
                        unset($mapping["dataSource"]);
                        $mapping["options"] = ["type"=>"hidden"];
                        $mapping["default"] = $parent_id;
                        $mappings[$table][$template][$key] = $mapping;
                        break;
                    }
                }
            }
            $this->view->setVar("mappings", $mappings[$table][$template]);

            $data = [];
            foreach($mappings[$table][$template] as $name=>$mapping) {
                $value = $vt_page->get($mapping["mapping"]);
                if($mapping["type"]=="array") {
                    $value = explode(";", $value);
                }
                if($value) { $data[$name] = $value; }
            }
            $this->view->setVar("data", $data);

            $label = "Edit article";
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('Pages/editform_html.php');
        }

    }
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
    require_once(__CA_MODELS_DIR__.'/ca_entities.php');
    require_once(__CA_MODELS_DIR__.'/ca_list_items.php');
    require_once(__CA_MODELS_DIR__.'/ca_object_labels.php');
    require_once(__CA_LIB_DIR__."/ca/Search/EntitySearch.php");
	error_reporting(E_ERROR);

 	class DoController extends ActionController {
 		# -------------------------------------------------------
  		protected $opo_config;		// plugin configuration file
        protected $opa_list_of_lists; // list of lists
        protected $opa_listIdsFromIdno; // list of lists
        protected $opa_locale; // locale id
		private $opo_list;
 		# -------------------------------------------------------
 		# Constructor
 		# -------------------------------------------------------

 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
            parent::__construct($po_request, $po_response, $pa_view_paths);

 			$this->opo_config = Configuration::load(__CA_APP_DIR__.'/plugins/Contribuer/conf/contribuer.conf');
 			
			$this->opo_list = new ca_lists("object_types");
        }

 		# -------------------------------------------------------
 		# Functions to render views
 		# -------------------------------------------------------
 		public function Add($type="") {
 			$id= $this->request->getParameter("id", pInteger);
			$this->view->setVar("template", $this->opo_config->get("template"));
            $this->view->setVar("mappings", $this->opo_config->get("mappings"));
            $this->render('index_html.php');
 		}

 		public function Create() {
 		    // TODO : test if already exist
            // - isbn
            // - title & author & date

            $vb_auto_numbering = $this->opo_config->get("auto_numbering");
            $vs_auto_numbering_prefix = $this->opo_config->get("auto_numbering_prefix");

            if($vb_auto_numbering) {
                $o_data = new Db();
                $query = "SELECT MAX(REPLACE(idno, '".$vs_auto_numbering_prefix."', '')*1) as idno FROM ca_objects WHERE idno LIKE '".$vs_auto_numbering_prefix."%'";
                $qr_result = $o_data->query($query);
                if($qr_result->nextRow()) {
                    $idno=$qr_result->get('idno');
                }
                if(!$idno) {
                    // No former object with this prefix
                    $idno = $vs_auto_numbering_prefix.'1';
                } else {
                    // We already have at least an object with this prefix, take the last and add 1 to it
                    $idno = $vs_auto_numbering_prefix.(((int) str_replace($vs_auto_numbering_prefix, "", $idno))*1+1);
                }
            }

            $vt_object = new ca_objects();
            $vt_object->setMode(ACCESS_WRITE); //Set access mode to WRITE
            $pn_locale_id='2'; //Set the locale
            $vt_object->set(array('access' => 1, 'status' => 3, 'idno' => $idno,'type_id' => $this->opo_config->get("type_id"),'locale_id'=>$pn_locale_id));//Define some intrinsic data.
            $id = $vt_object->insert();//Insert the object
            if(!$id) {
                //var_dump($vt_object->getErrors());
                $id= $this->request->getParameter("id", pInteger);
                $this->view->setVar("template", $this->opo_config->get("template"));
                $this->view->setVar("mappings", $this->opo_config->get("mappings"));
                $this->view->setVar("errors", $vt_object->getErrors());
                $this->render('index_html.php');
            } else {
                // The object is created, now adding the metadatas

                //var_dump($_POST);die();
                foreach ($this->opo_config->get("mappings") as $field=>$mapping) {
                    $value = $this->getRequest()->getParameter($field, pString);
                    if($value) {
                        $parts = explode(".",$mapping["mapping"]);
                        switch($parts[0]) {
                            case "ca_objects":
                                // OBJECT
                                if($parts[1] == "preferred_labels") {
                                    // LABEL
                                    $vt_object->addLabel(["name"=>$value], $pn_locale_id, null, 1);
                                    $vt_object->update();
                                } else if(sizeof($parts) == 2) {
                                    // OBJECT.FIELD
                                    $vt_object->addAttribute([$parts[1]=>$value], $parts[1]);
                                    $vt_object->update();
                                } else {
                                    // OBJECT.FIELD.SUBFIELD
                                    // TODO : group before insertion, as we may have multiple parts
                                    $vt_object->addAttribute([$parts[2]=>$value], $parts[1]);
                                    $vt_object->update();
                                }
                                break;
                            case "ca_entities":
                                $vt_entity = new ca_entities();
                                // try to load an existing entity
                                $vb_entity_loaded=$vt_entity->load(['idno' => $value, "deleted"=> 0]);
                                if(!$vb_entity_loaded) {
                                    // Not loaded, let's search
                                    $e_search = new EntitySearch();
                                    $qr_hits = $e_search->search("ca_entities.preferred_labels.displayname:\"".$value."\"");
                                    if(!$qr_hits->numHits()) {
                                        // NO ANSWER, CREATING
                                        $vt_entity->setMode(ACCESS_WRITE);
                                        $entity_base_values = array('access' => 1, 'status' => 3, 'idno' => $value,'type_id' => $mapping['type'],'locale_id'=>$pn_locale_id);
                                        $vt_entity->set($entity_base_values);
                                        $entity_id = $vt_entity->insert();
                                        if(!$entity_id) {
                                            die("Unable to create the entity. Please the database administrator");
                                        }
                                    } elseif($qr_hits->numHits()==1) {
                                        // Only one answer, let's use it
                                        $vt_entity->load($qr_hits->get("ca_entities.entity_id"));
                                    } else {
                                        // Multiple answers, we'll use the first one
                                        $vt_entity->load($qr_hits->get("ca_entities.entity_id"));
                                    }
                                }
                                $vt_entity->setMode(ACCESS_WRITE);
                                $vt_object->addRelationship("ca_entities", $entity_id, $mapping["relation"]);
                                $vt_object->update();
                                if($vt_object->errors()) {
                                    var_dump($vt_object->getErrors());
                                    //die();
                                }
                                break;
                        }

                    }
                }
                if($vt_object->errors()) {
                    $this->view->setVar("errors", $vt_object->getErrors());
                }
                $this->view->setVar("object_id", $id);
                $this->render('inserted_html.php');

            }
        }

        public function Additions() {

        }

        public function Review() {

        }

 	}
 ?>

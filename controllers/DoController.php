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
    require_once(__CA_MODELS_DIR__.'/ca_places.php');
    
    require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
    require_once(__CA_MODELS_DIR__.'/ca_list_items.php');
    require_once(__CA_MODELS_DIR__.'/ca_object_labels.php');
    require_once(__CA_LIB_DIR__."/ca/Search/EntitySearch.php");
    require_once(__CA_LIB_DIR__."/ca/Search/CollectionSearch.php");
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
 		public function Index($type="") {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
                $this->render('anonymous_index_html.php');
                return false;
            }
            foreach($this->getRequest()->getUser()->getUserGroups() as $group) {
                if($group["code"] == $this->opo_config->get("moderator_user_groups")) {
                    $this->view->setVar("is_moderator", true);
                } else {
                    $this->view->setVar("is_moderator", false);
                }
            }
            $id= $this->request->getParameter("id", pInteger);
            $this->view->setVar("template", $this->opo_config->get("template"));
            $this->view->setVar("mappings", $this->opo_config->get("mappings"));

            $contribution_filenames = scandir(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions");
            $contribution_filenames = array_diff($contribution_filenames, array('..', '.'));
            $contributions = [];
            foreach($contribution_filenames as $filename) {
	            $contrib_file_content = file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".$filename);
	            if(trim($contrib_file_content) == "[]") continue;
                $contrib = json_decode($contrib_file_content, TRUE);
                
                $user_id = $contrib["_user_id"];
                $timecode = $contrib["_timecode"];
                $vt_user = new ca_users($user_id);
                $user_name = $vt_user->get("ca_users.user_name");
                $contributions[] = ["type_id" => $contrib["type_id"], "_type" => $contrib["_type"], "title"=> "<b>".$contrib["title"]."</b><br/>".date('d/m/Y H:i', $timecode)." : ".$user_name, "filename"=>$filename];
            }
            $this->view->setVar("contributions", $contributions);
            
            $modifications_filenames = scandir(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications");
            $modifications_filenames = array_diff($modifications_filenames, array('..', '.'));
            $modifications = [];
            foreach($modifications_filenames as $filename) {
                $contrib_file_content = file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename);
	            if(trim($contrib_file_content) == "[]") continue;
                $contrib = json_decode($contrib_file_content, TRUE);
                //var_dump($contrib);die();
                $user_id = ($contrib["_user_id"] ? $contrib["_user_id"] : 1);
                $timecode = $filename;
                $vt_user = new ca_users($user_id);
                $user_name = $vt_user->get("ca_users.user_name");
                $modifications[] = ["type_id" => $contrib["type_id"], "_type" => $contrib["_type"], "title"=> date('d/m/Y H:i', $timecode)." : ".$user_name, "filename"=>$filename];
            }
            $this->view->setVar("modifications", $modifications);
            
            $this->render('index_index_html.php');
        }

		public function ModerateModification() {
			$filename = $this->request->getParameter("modification", pString);
			$this->view->setVar("filename", $filename);
			$modification = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename), TRUE);
			$this->view->setVar("modification", $modification);

            $this->render('moderate_modification_html.php');
		}
        public function AddIssue($type="") {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $id= $this->request->getParameter("id", pInteger);
            $this->view->setVar("template", $this->opo_config->get("template"));
            $this->view->setVar("mappings", $this->opo_config->get("mappings"));
            $mappings = $this->opo_config->get("mappings");
            $this->view->setVar("mappings", $mappings["issue"]);
            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('add_issue_html.php');
        }

        public function Add($type="") {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $id= $this->request->getParameter("id", pInteger);
			$type=$this->request->getParameter("type", pString);
			$parent_id = $this->request->getParameter("parent_id", pString);
			$this->view->setVar("parent_id", $parent_id);
			
			$this->view->setVar("template", $this->opo_config->get("template"));
            $mappings = $this->opo_config->get("mappings");
            
            // If we have parent_id, we need to override the template to disallow direct selection
            if($parent_id) {
	            foreach($mappings[$type] as $key=>$mapping) {
		            $target = explode(".", $mapping["mapping"])[1];
		            if($target == "parent_id") {
			            unset($mapping["dataSource"]);
			            $mapping["options"] = ["type"=>"hidden"];
			            $mapping["default"] = $parent_id;
			            $mappings[$type][$key] = $mapping;
			            break;
		            }
	            }
            }
            $this->view->setVar("mappings", $mappings[$type]);
            
            switch($type) {
	            case "magazine":
	            	$label = "create a new magazine";
	            	break;
				case "issue":
	            	$label = "create a new issue";
	            	break;
				case "article":
	            	$label = "create a new article";
	            	break;
				default:
	            	$label = "";
	            	break;
            }
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('add_html.php');
        }
        
        public function Form() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $id= $this->request->getParameter("id", pInteger);
            $table=$this->request->getParameter("table", pString);
            $this->view->setVar("table", $table);
			$type=$this->request->getParameter("type", pString);
			$this->view->setVar("type", $type);
			$parent_id = $this->request->getParameter("parent_id", pString);
			$this->view->setVar("parent_id", $parent_id);
			
			$this->view->setVar("template", $this->opo_config->get("template"));
            $mappings = $this->opo_config->get("form");
            
            // If we have parent_id, we need to override the template to disallow direct selection
            if($parent_id) {
	            foreach($mappings[$table][$type] as $key=>$mapping) {
		            $target = explode(".", $mapping["mapping"])[1];
		            if($target == "parent_id") {
			            unset($mapping["dataSource"]);
			            $mapping["options"] = ["type"=>"hidden"];
			            $mapping["default"] = $parent_id;
			            $mappings[$table][$type][$key] = $mapping;
			            break;
		            }
	            }
            }
            $this->view->setVar("mappings", $mappings[$table][$type]);
            
            switch($type) {
	            case "magazine":
	            	$label = "create a new magazine";
	            	break;
				case "issue":
	            	$label = "create a new issue";
	            	break;
				case "article":
	            	$label = "create a new article";
	            	break;
				default:
	            	$label = "create a new ".$type;
	            	break;
            }
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('addform_html.php');
        }

        public function Create() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }
			$vs_table = $this->request->getParameter("_table", pString);
			$this->view->setVar("table", $vs_table);
			$vs_type = $this->request->getParameter("_type", pString);
			$this->view->setVar("type", $vs_type);
			$vn_type_id = $this->request->getParameter("type_id", pString);
			
			$contribution_file = $this->request->getParameter("contribution", pString);
			$this->view->setVar("contribution", $contribution);
            
			$vn_user_id = $this->request->getParameter("_user_id", pString);
			$vt_user = new ca_users($vn_user_id);
            $user_name = $vt_user->get("ca_users.user_name");
            $this->view->setVar("user_name", $user_name);
            
            $timecode = $contribution["_timecode"];
            $this->view->setVar("timecode", $timecode);
            $this->view->setVar("date", date('d/m/Y H:i', $timecode));

            // TODO : test if already exist
            // - isbn
            // - title & author & date

            $vb_auto_numbering = $this->opo_config->get("auto_numbering");
            $vs_auto_numbering_prefix = $this->opo_config->get("auto_numbering_prefix");

            if(($vb_auto_numbering) &&($vs_table == "ca_objects")) {
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

            //$vt_object = new ca_objects();
            $vt_object = new $vs_table();
            $vt_object->setMode(ACCESS_WRITE); //Set access mode to WRITE
            $pn_locale_id='1'; //Set the locale ; en_US for Le Grand Jeu
            if($vs_table == "ca_places") {
            $vt_object->set(array('access' => 1, 'status' => 3, 'idno' => $idno,'type_id' => $vn_type_id,'locale_id'=>$pn_locale_id, 'hierarchy_id'=>151));//Define some intrinsic data.
            } else {
	            $vt_object->set(array('access' => 1, 'status' => 3, 'idno' => $idno,'type_id' => $vn_type_id,'locale_id'=>$pn_locale_id));//Define some intrinsic data.
            }
            
            $id = $vt_object->insert();//Insert the record
            $this->view->setVar("id", $id);
            
            if(!$id) {
                //var_dump($vt_object->getErrors());
                $id= $this->request->getParameter("id", pInteger);
                $this->view->setVar("template", $this->opo_config->get("template"));
                $this->view->setVar("mappings", $this->opo_config->get("mappings"));
                $this->view->setVar("errors", $vt_object->getErrors());
                $this->render('index_html.php');
            } else {
                // The object is created, now adding the metadatas
				$mappings = $this->opo_config->get("form");
				$mappings = $mappings[$vs_table][$vs_type];
                foreach ($mappings as $field=>$mapping) {
                    $value = $this->getRequest()->getParameter($field, pString);
                    if($value) {
                        $parts = explode(".",$mapping["mapping"]);
                        if($parts[0] == $vs_table) {
                                // OBJECT
                                if($parts[1] == "preferred_labels") {
                                    // LABEL
                                    if($vs_table == "ca_entities") {
	                                    $vt_object->addLabel(["displayname"=>$value], $pn_locale_id, null, 1);
	                                    $this->view->setVar("title", $value);
                                    } else {
	                                	$vt_object->addLabel(["name"=>$value], $pn_locale_id, null, 1);    
	                                	$this->view->setVar("title", $value);
                                    }
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
                        } else {
	                        	continue;
                                $table = $parts[0];
                                $vt_authority = new $table();
                                //$vt_authority = new ca_entities();
                                switch($parts[0]) {
                                    case "ca_entities":
                                        $authority_search = "EntitySearch";
                                        $authority_id_fieldname = "entity_id";
                                        $authority_type_id = 120;
                                        break;
                                    case "ca_objects":
                                        $authority_search = "ObjectSearch";
                                        $authority_id_fieldname = "object_id";
                                        $authority_type_id = 120;
                                        break;
                                    case "ca_collections":
                                        $authority_search = "CollectionSearch";
                                        $authority_id_fieldname = "collection_id";
                                        $authority_type_id = 120;
                                        break;
                                }
                                // try to load an existing entity
                                $vb_entity_loaded=$vt_authority->load(['idno' => $value, "deleted"=> 0]);
                                if(!$vb_entity_loaded) {
                                    // Not loaded, let's search
                                    $a_search = new $authority_search();
                                    $qr_hits = $a_search->search("$table.preferred_labels.displayname:\"".$value."\"");
                                    if($qr_hits->numHits() != 1) {
                                        // NO ANSWER OR MULTIPLES, CREATING
                                        $vt_authority->setMode(ACCESS_WRITE);
                                        $authority_base_values = array('access' => 1, 'status' => 3, 'idno' => $value,'type_id' => $mapping['type_id'],'locale_id'=>$pn_locale_id);
                                        $vt_authority->set($authority_base_values);
                                        $authority_id = $vt_authority->insert();
                                        if(!$authority_id) {
                                            $vt_authority = new $table();
                                            $vt_authority->setMode(ACCESS_WRITE);
                                            $vt_authority->set(["access"=>1, "status"=>3, "idno"=>$value,"type_id"=> $mapping['type_id'],"locale_id"=>"1"]);
                                            $authority_id = $vt_authority->insert();
                                            die("Unable to create the entity. Please contact the database administrator");
                                        } else {
                                            switch($parts[0]) {
                                                case "ca_entities":
                                                    $vt_authority->addLabel(array('displayname'=>$value, 'surname'=>$value),$pn_locale_id,null,true);
                                                    break;
                                                default:
                                                    $vt_authority->addLabel(array('name'=>$value),$pn_locale_id,null,true);
                                                    break;
                                            }
                                            if($vt_authority->numErrors()) {
                                                var_dump($vt_authority->getErrors());
                                                die();
                                            }
                                            $vt_authority->update();
                                        }
                                    } else {
                                        // Only one answer, let's use it
                                        $vt_authority->load($qr_hits->get("$table.$authority_id_fieldname"));
                                    }
                                } else {
                                    // Authority loaded

                                    //Check if authority has a label, if not set it to the idno
                                    $label = $vt_authority->getLabels();
                                    if(!$label) {
                                        // Safety update
                                        switch($parts[0]) {
                                            case "ca_entities":
                                                $vt_authority->addLabel(array('displayname'=>$value, 'surname'=>$value),$pn_locale_id,null,true);
                                                break;
                                            default:
                                                $vt_authority->addLabel(array('name'=>$value),$pn_locale_id,null,true);
                                                break;
                                        }
                                        $vt_authority->update();
                                    }
                                    $authority_id = $vt_authority->get("$table.$authority_id_fieldname");
                                }
                                $vt_authority->setMode(ACCESS_WRITE);
                                $vt_object->addRelationship("$table", $authority_id, $mapping["relation"]);
                                $authority_id = null;

                                $result = $vt_object->update();

                                if($vt_object->errors()) {
                                    var_dump($vt_object->getErrors());
                                    die();
                                }
                                break;
                        }
                    }
                }
                if($vt_object->errors()) {
                    $this->view->setVar("errors", $vt_object->getErrors());
                    var_dump($vt_object->getErrors());
                    die();
                } else {
	                // NO ERROR, DELETE THE CONTRIBUTION
	                unlink(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".$contribution_file);
                }
                $this->view->setVar("object_id", $id);

                if($this->opo_config->get("media_upload") == 1) {
                    $this->redirect(__CA_URL_ROOT__."/index.php/Contribuer/Do/AddMedia/object_id/".$id);
                } else {
                    $this->render('inserted_html.php');
                }


            }
        }

        public function Moderate() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $contribution_file = $this->getRequest()->getParameter("contribution", pString);

            $contribution = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".$contribution_file), TRUE);
            $this->view->setVar("data", $contribution);

            $user_id = $contribution["_user_id"];
            $type_id = $contribution["type_id"];
            
            $vt_user = new ca_users($user_id);
            $user_name = $vt_user->get("ca_users.user_name");
            $this->view->setVar("user_id", $user_id);
            $this->view->setVar("user_name", $user_name);
            $timecode = $contribution["_timecode"];
            $type = $contribution["_type"];
            $this->view->setVar("table", $table);
            $this->view->setVar("timecode", $timecode);
            $this->view->setVar("date", date('d/m/Y H:i', $timecode));

            $id= $this->request->getParameter("id", pInteger);

            $this->view->setVar("template", $this->opo_config->get("template"));
            if($contribution["_table"]) {
	            $table = $contribution["_table"];
	            $mappings = $this->opo_config->get("form");
	            $mappings = $mappings[$contribution["_table"]];
            } else {
				$table="ca_objects";
	            $mappings = $this->opo_config->get("form");
	            $mappings = $mappings["ca_objects"];
            }
	        $label = "new ".$type;
            $this->view->setVar("mappings", $mappings[$type]);
            $this->view->setVar("label", $label);

            $contribution_file = $this->getRequest()->getParameter("contribution", pString);
            $this->view->setVar("json_file", $contribution_file);
            
            $this->render('moderate_html.php');
        }

        public function ModerateOld() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $contribution_file = $this->getRequest()->getParameter("json_file", pString);
            $contribution = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".$contribution_file), TRUE);

            $contribution_type = $this->getRequest()->getParameter("type", pString);
            $this->view->setVar("contribution_type", $contribution_type);

            $user_id = $contribution["_user_id"];
            $vt_user = new ca_users($user_id);
            $user_name = $vt_user->get("ca_users.user_name");
            $this->view->setVar("user_id", $user_id);
            $this->view->setVar("user_name", $user_name);

            $timecode = $contribution["_timecode"];
            $this->view->setVar("timecode", $timecode);
            $this->view->setVar("date", date('d/m/Y H:i', $timecode));

            $_GET = $contribution;
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
            $pn_locale_id='1'; //Set the locale ; en_US for Le Grand Jeu
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

                $mappings = $this->opo_config->get("mappings");
                $mappings = $mappings[$contribution_type];

                foreach ($mappings as $field=>$mapping) {
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

                                    $this->view->setVar("title", $value);
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
                            case "ca_collections":
                                $table = $parts[0];
                                $vt_authority = new $table();
                                //$vt_authority = new ca_entities();
                                switch($parts[0]) {
                                    case "ca_entities":
                                        $authority_search = "EntitySearch";
                                        $authority_id_fieldname = "entity_id";
                                        $authority_type_id = 120;
                                        break;
                                    case "ca_collections":
                                        $authority_search = "CollectionSearch";
                                        $authority_id_fieldname = "collection_id";
                                        $authority_type_id = 120;
                                        break;
                                }
                                // try to load an existing entity
                                $vb_entity_loaded=$vt_authority->load(['idno' => $value, "deleted"=> 0]);
                                if(!$vb_entity_loaded) {
                                    // Not loaded, let's search
                                    $a_search = new $authority_search();
                                    $qr_hits = $a_search->search("$table.preferred_labels.displayname:\"".$value."\"");
                                    if($qr_hits->numHits() != 1) {
                                        // NO ANSWER OR MULTIPLES, CREATING
                                        $vt_authority->setMode(ACCESS_WRITE);
                                        $authority_base_values = array('access' => 1, 'status' => 3, 'idno' => $value,'type_id' => $mapping['type_id'],'locale_id'=>$pn_locale_id);
                                        $vt_authority->set($authority_base_values);
                                        $authority_id = $vt_authority->insert();
                                        if(!$authority_id) {
                                            $vt_authority = new $table();
                                            $vt_authority->setMode(ACCESS_WRITE);
                                            $vt_authority->set(["access"=>1, "status"=>3, "idno"=>$value,"type_id"=> $mapping['type_id'],"locale_id"=>"1"]);
                                            $authority_id = $vt_authority->insert();
                                            die("Unable to create the entity. Please contact the database administrator");
                                        } else {
                                            switch($parts[0]) {
                                                case "ca_entities":
                                                    $vt_authority->addLabel(array('displayname'=>$value, 'surname'=>$value),$pn_locale_id,null,true);
                                                    break;
                                                default:
                                                    $vt_authority->addLabel(array('name'=>$value),$pn_locale_id,null,true);
                                                    break;
                                            }
                                            if($vt_authority->numErrors()) {
                                                var_dump($vt_authority->getErrors());
                                                die();
                                            }
                                            $vt_authority->update();
                                        }
                                    } else {
                                        // Only one answer, let's use it
                                        $vt_authority->load($qr_hits->get("$table.$authority_id_fieldname"));
                                    }
                                } else {
                                    // Authority loaded

                                    //Check if authority has a label, if not set it to the idno
                                    $label = $vt_authority->getLabels();
                                    if(!$label) {
                                        // Safety update
                                        switch($parts[0]) {
                                            case "ca_entities":
                                                $vt_authority->addLabel(array('displayname'=>$value, 'surname'=>$value),$pn_locale_id,null,true);
                                                break;
                                            default:
                                                $vt_authority->addLabel(array('name'=>$value),$pn_locale_id,null,true);
                                                break;
                                        }
                                        $vt_authority->update();
                                    }
                                    $authority_id = $vt_authority->get("$table.$authority_id_fieldname");
                                }
                                $vt_authority->setMode(ACCESS_WRITE);
                                $vt_object->addRelationship("$table", $authority_id, $mapping["relation"]);
                                $authority_id = null;

                                $result = $vt_object->update();

                                if($vt_object->errors()) {
                                    var_dump($vt_object->getErrors());
                                    die();
                                }
                                break;
                        }

                    }
                }
                if($vt_object->errors()) {
                    $this->view->setVar("errors", $vt_object->getErrors());
                } else {
                    // Insertion OK, delete the contribution
                    unlink(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".$contribution_file);
                }
                $this->view->setVar("object_id", $id);
                print "</PRE>";
                if($this->opo_config->get("media_upload") == 1) {
                    $this->redirect(__CA_URL_ROOT__."/index.php/Contribuer/Do/AddMedia/id/".$id);
                } else {
                    $this->render('inserted_html.php');
                }


            }
        }

        public function DeleteContribution() {
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }
            $contribution_file = $this->getRequest()->getParameter("contribution", pString);
            $this->view->setVar("contribution", $contribution_file);
            unlink(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".$contribution_file);
            //print json_encode(["result"=>"success", "message"=>$contribution_file." deleted"]);
			$this->render('deleted_html.php');

        }
        
        public function ValidateModifications() {
	        $filename = $this->request->getParameter("modification", pString);
			$modification = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename), TRUE);
	        
	        $fields = $this->getRequest()->getParameter("fields", pString);
	        $fields = explode(",",$fields);
	        
	        $table = $this->getRequest()->getParameter("_table", pString);
	        $pn_locale_id='1'; //Set the locale ; en_US for Le Grand Jeu
            
	        if($table == "ca_objects") {
		    	$id = $this->getRequest()->getParameter("ca_objects_object_id", pString);    
		        $vt_object = new ca_objects($id);
				$vt_object->setMode(ACCESS_WRITE); //Set access mode to WRITE
				//var_dump($fields);die();
				foreach($fields as $field) {
					$value = $this->getRequest()->getParameter($field, pString);
					$field_code = str_replace($table."_", "", $field);
					switch($field_code) {
						case "preferred_labels_name":
						case "preferred_labels":
							$vt_object->removeAllLabels(__CA_LABEL_TYPE_PREFERRED__);
							$vt_object->update();
							$vt_object->addLabel(["name"=>$value], $pn_locale_id, null, 1);
							$vt_object->update();
								
						break;
						case "lang":
							// TODO : execute this thing if the metadata is of list type
							$vt_object->addAttribute([$field_code=>$value], $field_code);
							$vt_object->update();	
						break;
						default:
							$vt_object->removeAttributes($field_code);
							$vt_object->update();
							$vt_object->addAttribute([$field_code=>$value], $field_code);
							$vt_object->update();	
						break;
					}
				}
				$result = unlink(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename);
				$this->response->setRedirect(caNavUrl($this->request, "Detail", "objects", $id));
	        }
	        $this->render("validate_modifications_html.php");
        }

        public function DeleteModification() {
	        $filename = $this->request->getParameter("modification", pString);
			$modification = unlink(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename);
	        
			$this->response->setRedirect(__CA_URL_ROOT__."/index.php/Contribuer/Do/Index");
        }
        
        public function SendToModeration() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

			$json = json_encode($_POST);
			if($json == "[]") {
				var_dump($json);
				var_dump($_POST);
				die();
			}
            
            //print "...";
            $date = time();
			$octets = file_put_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".$date.".json", $json);   
			
			$this->view->setVar("octets", $octets);
            //var_dump(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/contributions/".time().".json");
            $this->render('sent_to_moderation_html.php');
        }

        public function SendModificationToModeration() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $json = json_encode($_POST);
            $this->view->setVar("result", $json);
           //print "...";
           $time = time();
            file_put_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$time.".json", $json);
            $content = file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$time.".json");
            sleep(1);
            $this->view->setVar("time", $time);
            $this->view->setVar("content", $content);
            $this->render('modification_sent_to_moderation_html.php');
        }

        public function AddMedia() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }


            //$this->
            $id = $this->request->getParameter("object_id", pInteger);
            $this->view->setVar("id", $id);
            $this->render('media_html.php');
        }

        public function PostMedia() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }


            $ds = DIRECTORY_SEPARATOR;  //1
            $storeFolder = 'uploads';   //2
            $id = $this->request->getParameter("id", pInteger);
            $pn_locale_id='1'; //Set the locale ; en_US for Le Grand Jeu

            if (!empty($_FILES) && $id) {
                $vt_object = new ca_objects($id);
                $vt_object->setMode(ACCESS_WRITE);

                $tempFile = $_FILES['file']['tmp_name'];          //3
                $uploaddir = __CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias/";
                $uploadfile = $uploaddir . basename($_FILES['file']['name']);

                if (move_uploaded_file($tempFile, $uploadfile)) {
                    while(!is_file($uploadfile)) {
	                    print ".";
	                    print filesize($uploadfile);
                    }
                } else {
                    echo "Possible file upload attack!\n";
                }

                $rep_id = $vt_object->addRepresentation($uploadfile, $this->opo_config->get("media_type_id"), $pn_locale_id, 3, 1, 1,  $pa_values=["idno"=>basename($_FILES['file']['name'])]);
                var_dump([$uploadfile, $this->opo_config->get("media_type_id"), $pn_locale_id, 3, 1, 1,  $pa_values=["idno"=>basename($_FILES['file']['name'])]]);
                var_dump($uploadfile);
                var_dump(filesize($uploadfile));
                var_dump($vt_object->getErrors());
                $result = $vt_object->update();
                var_dump($rep_id);
                var_dump($result);
                //move_uploaded_file($tempFile, $targetFile); //6
            }
            return true;

        }
        public function Additions() {

        }

        public function Review() {

        }

        public function Delete() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }


            $id = $this->request->getParameter("id", pInteger);
            $this->view->setVar("id", $id);
            $this->render('delete_confirm_html.php');
        }

        public function DeleteConfirmed() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }


            $id = $this->request->getParameter("id", pInteger);
            $this->view->setVar("id", $id);$vt_object = new ca_objects($id);
            $vt_object->setMode(ACCESS_WRITE);
            $vt_object->delete();
            $errors = $vt_object->getErrors();
            if($errors) {
                $this->view->setVar("errors", $errors);
                $this->render('delete_errors_html.php');
            } else {
                $this->render('deleted_html.php');
            }

        }
 	}

 ?>

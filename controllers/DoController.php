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
	if(__CollectiveAccess_Schema_Rev__<154) $old="/ca"; else $old="";

	ini_set("display_errors", 1);
    error_reporting(E_ERROR);
    require_once(__CA_MODELS_DIR__.'/ca_lists.php');
    require_once(__CA_MODELS_DIR__.'/ca_objects.php');
    require_once(__CA_MODELS_DIR__.'/ca_entities.php');
    require_once(__CA_MODELS_DIR__.'/ca_places.php');
    
    require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
    require_once(__CA_MODELS_DIR__.'/ca_list_items.php');
    require_once(__CA_MODELS_DIR__.'/ca_object_labels.php');
    require_once(__CA_LIB_DIR__."$old/Search/EntitySearch.php");
    require_once(__CA_LIB_DIR__."$old/Search/CollectionSearch.php");
	error_reporting(E_ERROR);

 	class DoController extends ActionController {
 		# -------------------------------------------------------
  		protected $opo_config;		// plugin configuration file
        protected $opa_list_of_lists; // list of lists
        protected $opa_listIdsFromIdno; // list of lists
        protected $opa_locale; // locale id
		private $opo_list;
        private $plugin_path;
 		# -------------------------------------------------------
 		# Constructor
 		# -------------------------------------------------------

 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
            parent::__construct($po_request, $po_response, $pa_view_paths);

            if (is_file(__CA_THEME_DIR__.'/conf/contribuer.conf')) {
                $this->opo_config = Configuration::load(__CA_THEME_DIR__.'/conf/contribuer.conf');
            } else {
                $this->opo_config = Configuration::load(__CA_APP_DIR__.'/plugins/Contribuer/conf/contribuer.conf');
            }

            $this->plugin_path = __CA_APP_DIR__ . '/plugins/Contribuer';
			$this->opo_list = new ca_lists("object_types");

            // Extracting theme name to properly handle views in distinct theme dirs
            $vs_theme_dir = explode("/", $po_request->getThemeDirectoryPath());
            $vs_theme = end($vs_theme_dir);
            $this->opa_view_paths[] = $this->plugin_path."/themes/".$vs_theme."/views";
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
            
            $medias_filenames = scandir(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias");
            $medias_filenames = array_diff($medias_filenames, array('..', '.'));
            $medias = [];
            foreach($medias_filenames as $filename) {         
	            // Ignore non JSON files
	            $ext = pathinfo($filename, PATHINFO_EXTENSION);
	            if($ext != "json") continue;
	            
                $contrib_file_content = file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias/".$filename);
	            if(trim($contrib_file_content) == "[]") continue;
                $contrib = json_decode($contrib_file_content, TRUE);
                //var_dump($contrib);die();
                $user_id = ($contrib["_user_id"] ? $contrib["_user_id"] : 1);
                $timecode = $filename;
                $vt_user = new ca_users($user_id);
                $user_name = $vt_user->get("ca_users.user_name");
                $medias[] = ["id" => $contrib["id"], "image" => $contrib["image"], "filename"=>$filename];
            }
            $this->view->setVar("medias", $medias);


            $this->render('index_index_html.php');
        }

		public function ModerateModification() {
			$filename = $this->request->getParameter("modification", pString);
			$this->view->setVar("filename", $filename);
			$modification = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename), TRUE);
			$this->view->setVar("modification", $modification);

			// Exiting if anonymous contributions are not allowed
			if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
				//$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
			}

			$id= $this->request->getParameter("id", pInteger);
			$this->view->setVar("id", $id);
			$table=$modification["_table"];
			$this->view->setVar("table", $table);
			$type=$modification["_type"];
			$this->view->setVar("type", $type);

			$this->view->setVar("user_id", $modification["_user_id"]);
			$this->view->setVar("timecode", $modification["_timecode"]);

			$mappings = $this->opo_config->get("form");
			$this->view->setVar("mappings", $mappings[$table][$type]);

			$id = $modification["_id"];
			$this->view->setVar("id", $id);
			if(!$id) {
				//$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
			}

			$vt_object = new ca_objects($id);

			$this->view->setVar("template", $this->opo_config->get("template"));
			$mappings = $this->opo_config->get("form");
			$this->view->setVar("mappings", $mappings[$table][$type]);
			$data = [];
			foreach($mappings[$table][$type] as $name=>$mapping) {
				$value = $vt_object->get($mapping["mapping"]);
				$data[$name] = $value;
			}
			$this->view->setVar("original", $data);
            $this->render('moderate_modification_html.php');
		}

		public function ModerateMedia() {
			$filename = $this->request->getParameter("contribution", pString);
			$this->view->setVar("filename", $filename);
			$media = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias/".$filename), TRUE);
			$this->view->setVar("media", $media);

			// Exiting if anonymous contributions are not allowed
			if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
				$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
			}
			$id = $media["id"];
			$this->view->setVar("id", $id);
			$this->view->setVar("image", $media["image"]);
			$this->view->setVar("filename", $filename);
			if(!$id) {
				$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
			}
			$vt_object = new ca_objects($id);
			$actual = $vt_object->get("ca_object_representations.media.large.url");
			$this->view->setVar("actual", $actual);

            $this->render('moderate_media_html.php');
		}

		public function DeleteMediaContribution() {
			$filename = $this->request->getParameter("contribution", pString);
			$media = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias/".$filename), TRUE);
			
			// Delete json & media
			unlink(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias/".$filename);
			unlink(__CA_BASE_DIR__."/upload/files/".$media["image"]);
			
			// Redirect
			$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
		}		

		public function ValidateMediaContribution() {
			$filename = $this->request->getParameter("contribution", pString);
			$this->view->setVar("filename", $filename);
			$this->render('validated_media_contribution_html.php');

			//$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
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

        public function Help() {
            $this->render('help_html.php');
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
	            case "edition":
	            	$label = "create a new print run";
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

        public function EditForm() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $id= $this->request->getParameter("id", pInteger);
			$this->view->setVar("id", $id);
            $table=$this->request->getParameter("table", pString);
            $this->view->setVar("table", $table);
			$type=$this->request->getParameter("type", pString);
			$this->view->setVar("type", $type);
			$parent_id = $this->request->getParameter("parent_id", pString);
			$this->view->setVar("parent_id", $parent_id);

			$id = $this->request->getParameter("id", pString);
			$this->view->setVar("id", $id);
            if(!$id) {
	            //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }
            
            $vt_item = new $table($id);
			
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
            
            $data = [];
            foreach($mappings[$table][$type] as $name=>$mapping) {
				$value = $vt_item->get($mapping["mapping"]);
            	if($mapping["type"]=="array") {
					$value = explode(";", $value);
				}
	            if($value) { $data[$name] = $value; }
            }
            $this->view->setVar("data", $data);
            
            switch($type) {
	            case "magazine":
	            	$label = "Edit reference : magazine";
	            	break;
				case "issue":
	            	$label = "Edit reference : issue";
	            	break;
				case "article":
	            	$label = "Edit reference : article";
	            	break;
				default:
	            	$label = "Edit reference : ".$type;
	            	break;
            }
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('editform_html.php');
        }

        public function EditMediaForm() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $id= $this->request->getParameter("id", pInteger);
			$this->view->setVar("id", $id);
            $table=$this->request->getParameter("table", pString);
            $this->view->setVar("table", $table);
			$type=$this->request->getParameter("type", pString);
			$this->view->setVar("type", $type);
			$parent_id = $this->request->getParameter("parent_id", pString);
			$this->view->setVar("parent_id", $parent_id);

			$id = $this->request->getParameter("id", pString);
			$this->view->setVar("id", $id);
            if(!$id) {
	            //$this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }
            
            $vt_object = new ca_objects($id);

            // Exit to specific display if we have a review, cover is taken from the first issue
            if($vt_object->get("ca_objects.type_id") == "24") return $this->render('editmediaform_reviews_html.php');
			
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
            
            $data = [];
            foreach($mappings[$table][$type] as $name=>$mapping) {
				$value = $vt_object->get($mapping["mapping"]);
            	if($mapping["type"]=="array") {
					$value = explode(";", $value);
				}
	            if($value) { $data[$name] = $value; }
            }
            $this->view->setVar("data", $data);
            
            switch($type) {
	            case "magazine":
	            	$label = "Image : magazine";
	            	break;
				case "issue":
	            	$label = "Image : issue";
	            	break;
				case "article":
	            	$label = "Image : article";
	            	break;
				default:
	            	$label = "Image : ".$type;
	            	break;
            }
            $this->view->setVar("label", $label);

            $this->view->setVar("user_id", $this->request->getUserID());
            $this->view->setVar("timecode", time());
            $this->render('editmediaform_html.php');
        }
        
        public function EditMedia() {
            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $id= $this->request->getParameter("id", pInteger);
            $image= $this->request->getParameter("image", pString);

            // Exiting if anonymous contributions are not allowed
            if(!$this->request->getUserID() && ($this->opo_config->get("allow_anonymous_contributions", pInteger) == 0)) {
	            $this->response->setRedirect(caNavUrl($this->request, "Contribuer", "Do", "Index"));
            }

            $json = json_encode($_POST);
            $this->view->setVar("result", $json);
           //print "...";
           	$time = time();
            file_put_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias/".$time.".json", $json);
            $content = file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/medias/".$time.".json");
            sleep(1);
            $this->view->setVar("time", $time);
            $this->view->setVar("content", $content);
            $this->render('editmedia_sent_to_moderation_html.php');
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

            $vt_object = new ca_objects();
            //$vt_object = new $vs_table();
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
		            if($contribution["type_id"] == 265) { $type = "article"; }
            }
	        $label = "new ".$type;
            $this->view->setVar("mappings", $mappings[$type]);
            $this->view->setVar("label", $label);

            $contribution_file = $this->getRequest()->getParameter("contribution", pString);
            $this->view->setVar("json_file", $contribution_file);
            
            $this->render('moderate_html.php');
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
 			error_reporting(E_ERROR);
 			$filename = $this->request->getParameter("modification", pString);

 			// We could take the file
 			// $modification = json_decode(file_get_contents(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename), TRUE);

			// ... but better is the updated posted version
			$modification = $_POST;

			$table = $this->getRequest()->getParameter("_table", pString);
	        $pn_locale_id='1'; //Set the locale ; en_US for Le Grand Jeu

	        // TODO : MIEUX GERER LES TYPES
			$type = $modification["_type"];

			$mappings = $this->opo_config->get("form");

			error_reporting(E_ERROR);
			ini_set('display_errors', true);

	        if($table == "ca_objects") {
		    	$id = $modification["_id"];
		    	if(!$id) {
		    		die("Invalid contribution file. No ID defined. Please report to database administrators.");
				}
		        $vt_object = new ca_objects($id);
				$vt_object->setMode(ACCESS_WRITE); //Set access mode to WRITE
				//var_dump($fields);die();
				foreach($mappings[$table][$type] as $name=>$mapping) {
					$field=$mapping["mapping"];

					$field_code = str_replace($table.".", "", $field);
					// Default : mapping equals the fields code
					$value = $modification[$name];
					switch($field_code) {
						// Intrinsic fields
						case "type_id":
						case "access":
						case "status":
						case "locale_id":
						case "parent_id":
							$vt_object->set([$field_code=>$value]);
							$vt_object->update;
							break;
						case "preferred_labels":
							$vt_object->removeAllLabels(__CA_LABEL_TYPE_PREFERRED__);
							$vt_object->addLabel(["name"=>$value], $pn_locale_id, null, 1);
							$vt_object->update();
							break;
						case "nonpreferred_labels":
							$vt_object->removeAllLabels(__CA_LABEL_TYPE_NONPREFERRED__);
							$vt_object->addLabel(["name"=>$value], $pn_locale_id, null, 0);
							$vt_object->update();
							break;
						default:
							// Skip if no modification
							if($value == $vt_object->get($field_code)) continue;

							// Remove former value
							$vt_object->removeAttributes($field_code);
							$vt_object->update();

							// No value, cleanup done skip what's next
							if(!$value) continue;

							$vt_object->addAttribute([$field_code=>$value], $field_code);
							$vt_object->update();
						break;
					}
				}
				$result = unlink(__CA_BASE_DIR__."/app/plugins/Contribuer/temp/modifications/".$filename);
				$vt_object->clearInstanceCacheForID($id);
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

			$json = $_POST["alpaca_serialization"];
			//$json = str_replace('\"', "''", $json);
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

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
 
	class contribuerPlugin extends BaseApplicationPlugin {
		# -------------------------------------------------------
		protected $description = 'Contribuer for CollectiveAccess';
		# -------------------------------------------------------
		private $opo_config;
		private $ops_plugin_path;
		# -------------------------------------------------------
		public function __construct($ps_plugin_path) {
			$this->ops_plugin_path = $ps_plugin_path;
			$this->description = _t('Contribuer plugin');
			parent::__construct();
			$this->opo_config = Configuration::load($ps_plugin_path.'/conf/contribuer.conf');
		}
		# -------------------------------------------------------
		/**
		 * Override checkStatus() to return true - the statisticsViewerPlugin always initializes ok... (part to complete)
		 */
		public function checkStatus() {
			return array(
				'description' => $this->getDescription(),
				'errors' => array(),
				'warnings' => array(),
				'available' => ((bool)$this->opo_config->get('enabled'))
			);
		}
		# -------------------------------------------------------
		/**
		 * Add plugin user actions
		 */
		static function getRoleActionList() {
			return array();
		}
        # -------------------------------------------------------
	}
?>
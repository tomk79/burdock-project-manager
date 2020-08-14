<?php
/**
 * Pickles 2 - Burdock Project Manager
 */
namespace tomk79\picklesFramework2\burdock\projectManager;

/**
 * Burdock Project Manager: Project class
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class project{

	/** main object */
	private $main;

	/** Project ID */
	private $project_id;

	/** Path to Burdock Data Directory */
	private $realpath_bd_data;

	/**
	 * Constructor
	 */
	public function __construct( $main, $realpath_bd_data, $project_id ){
		$this->main = $main;
		$this->realpath_bd_data = $realpath_bd_data;
		$this->project_id = $project_id;
	}

	/**
	 * ブランチオブジェクトを生成
	 */
	public function branch( $branch_name, $division = "preview" ){
		$realpath_projectroot_dir = false;
		switch( strtolower($division) ){
			case 'preview':
				$realpath_projectroot_dir = $this->realpath_bd_data.'repositories/'.urlencode($this->project_id).'---'.urlencode($branch_name).'/';
				break;
			case 'staging':
				$realpath_projectroot_dir = $this->realpath_bd_data.'stagings/'.urlencode($this->project_id).'---'.urlencode($branch_name).'/';
				break;
		}
		if( !$realpath_projectroot_dir ){
			return false;
		}

		$branch = new project_branch($this->main, $realpath_projectroot_dir);
		return $branch;
	}

	/**
	 * セットアップ時の要求内容を取得する
	 */
	public function get_setup_request(){
		$realpath_json = $this->realpath_bd_data.'projects/'.urlencode($this->project_id).'/setup_request.json';
		if( !is_file($realpath_json) || !is_readable($realpath_json) ){
			$json = new \stdClass();
			$json->git_remote = null;
			$json->git_user_name = null;
			$json->git_password = null;
			$json->composer_vendor_name = null;
			$json->composer_project_name = null;
			return $json;
		}
		$json_string = file_get_contents($realpath_json);
		$json = json_decode($json_string);
		return $json;
	}


	/**
	 * セットアップ時の要求内容を取得する
	 */
	public function save_setup_request($json){
		if( !is_object($json) && !is_array($json) ){
			return false;
		}

		$realpath_json = $this->realpath_bd_data.'projects/'.urlencode($this->project_id).'/setup_request.json';
		if( !is_dir( dirname($realpath_json) ) ){
			$this->main->fs()->mkdir_r(dirname($realpath_json));
		}

		$json_string = json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		$result = $this->main->fs()->save_file($realpath_json, $json_string);
		return $result;
	}

}

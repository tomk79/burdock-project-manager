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

	/** filesystem utility */
	private $fs;

	/** Path to Burdock Data Directory */
	private $realpath_bd_data;

	/**
	 * Constructor
	 */
	public function __construct( $main, $fs, $realpath_bd_data, $project_id ){
		$this->main = $main;
		$this->fs = $fs;
		$this->realpath_bd_data = $realpath_bd_data;
		$this->project_id = $project_id;
	}

	/**
	 * 環境個別の状態チェック
	 */
	public function status( $division, $branch_name ){

		$realpath_projectroot_dir = false;
		switch( strtolower($division) ){
			case 'preview':
				$realpath_projectroot_dir = $this->realpath_bd_data.'repositories/'.urlencode($this->project_id).'---'.urlencode($branch_name).'/';
				break;
			case 'staging':
				$realpath_projectroot_dir = $this->realpath_bd_data.'stagings/'.urlencode($this->project_id).'---'.urlencode($branch_name).'/';
				break;
		}

		$status = new \stdClass();

		$status->api = new \stdClass();
		$status->api->available = false;
		$status->api->version = false;
		$status->api->is_sitemap_loaded = false;

		$status->px2dthelper = new \stdClass();
		$status->px2dthelper->available = false;
		$status->px2dthelper->version = false;
		$status->px2dthelper->is_sitemap_loaded = false;

		$status->pathExists = $this->fs->is_dir($realpath_projectroot_dir);
		$status->pathContainsFileCount = false;

		if( $status->pathExists ){
			$status->pathContainsFileCount = 0;
			$ls = $this->fs->ls($realpath_projectroot_dir);
			foreach( $ls as $basename ){
				switch( $basename ){
					case '.DS_Store':
					case 'Thumbs.db':
						break;
					default:
						$status->pathContainsFileCount ++;
						break;
				}
			}
			unset($ls, $basename);
		}

		$status->composerJsonExists = false;
		$path_entry_script = '.px_execute.php';
		$path_home_dir = 'px-files/';
		if($status->pathExists && $this->fs->is_file($realpath_projectroot_dir.'composer.json')){
			$status->composerJsonExists = true;
			$tmp_str_composerJson = file_get_contents($realpath_projectroot_dir.'composer.json');
			$tmp_obj_composerJson = json_decode( $tmp_str_composerJson );
			try{
				if( is_object($tmp_obj_composerJson) && property_exists($tmp_obj_composerJson, 'extra') && property_exists($tmp_obj_composerJson, 'px2package') && $tmp_obj_composerJson->extra->px2package->path ){
					$path_entry_script = $tmp_obj_composerJson->extra->px2package->path;
				}
			}catch(Exception $e){}
		}

		$status->entryScriptExists = false;
		if($status->pathExists && $this->fs->is_file($realpath_projectroot_dir.$path_entry_script)){
			$status->entryScriptExists = true;
		}
		$status->homeDirExists = false;
		if($status->pathExists && $this->fs->is_dir($realpath_projectroot_dir.$path_home_dir)){
			$status->homeDirExists = true;
		}


		return $status;
	}
}

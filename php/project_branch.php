<?php
/**
 * Pickles 2 - Burdock Project Manager
 */
namespace tomk79\picklesFramework2\burdock\projectManager;

/**
 * Burdock Project Manager: Project Branch class
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class project_branch{

	/** main object */
	private $main;

	/** Path to Burdock Data Directory */
	private $realpath_projectroot_dir;

	/**
	 * entry script のパス
	 *
	 * `$realpath_projectroot_dir` を起点とした相対パスで格納します。
	 */
	private $path_entry_script;

	/**
	 * home directory のパス
	 *
	 * `$realpath_projectroot_dir` を起点とした相対パスで格納します。
	 */
	private $path_home_dir;

	/**
	 * プロジェクト情報のキャッシュ
	 */
	private $pjInfo;

	/**
	 * Constructor
	 */
	public function __construct( $main, $realpath_projectroot_dir ){
		$this->main = $main;
		$this->realpath_projectroot_dir = $realpath_projectroot_dir;
	}


	/**
	 * realpath_projectroot_dir を取得する
	 */
	public function get_realpath_projectroot_dir(){
		return $this->realpath_projectroot_dir;
	}

	/**
	 * 環境個別の状態チェック
	 */
	public function status(){

		// initialize
		$status = new \stdClass();

		$status->api = new \stdClass();
		$status->api->available = false;
		$status->api->version = false;
		$status->api->is_sitemap_loaded = false;

		$status->px2dthelper = new \stdClass();
		$status->px2dthelper->available = false;
		$status->px2dthelper->version = false;
		$status->px2dthelper->is_sitemap_loaded = false;

		$status->pathExists = $this->main->fs()->is_dir($this->realpath_projectroot_dir);
		$status->pathContainsFileCount = false;
		$status->composerJsonExists = false;
		$status->entryScriptExists = false;
		$status->homeDirExists = false;
		$status->confFileExists = false;
		$status->px2DTConfFileExists = false;
		$status->vendorDirExists = false;
		$status->isPxStandby = false;
		$status->gitDirExists = false;
		$status->guiEngineName = null;


		// Start
		if( $status->pathExists ){
			$status->pathContainsFileCount = 0;
			$ls = $this->main->fs()->ls($this->realpath_projectroot_dir);
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

		$this->path_entry_script = '.px_execute.php';
		$this->path_home_dir = 'px-files/';
		if($status->pathExists && $this->main->fs()->is_file($this->realpath_projectroot_dir.'composer.json')){
			$status->composerJsonExists = true;
			$tmp_str_composerJson = file_get_contents($this->realpath_projectroot_dir.'composer.json');
			$tmp_obj_composerJson = json_decode( $tmp_str_composerJson );
			try{
				if( is_object($tmp_obj_composerJson) && property_exists($tmp_obj_composerJson, 'extra') && property_exists($tmp_obj_composerJson->extra, 'px2package') && $tmp_obj_composerJson->extra->px2package->path ){
					$this->path_entry_script = $tmp_obj_composerJson->extra->px2package->path;
				}
				if( is_object($tmp_obj_composerJson) && property_exists($tmp_obj_composerJson, 'extra') && property_exists($tmp_obj_composerJson->extra, 'px2package') && $tmp_obj_composerJson->extra->px2package->path_homedir ){
					$this->path_home_dir = $tmp_obj_composerJson->extra->px2package->path_homedir;
				}
			}catch(Exception $e){}
		}

		if( $status->pathExists ){
			if($this->main->fs()->is_file($this->realpath_projectroot_dir.$this->path_entry_script)){
				$status->entryScriptExists = true;
			}

			if($this->main->fs()->is_dir($this->realpath_projectroot_dir.$this->path_home_dir)){
				$status->homeDirExists = true;
				if($this->main->fs()->is_file($this->realpath_projectroot_dir.$this->path_home_dir.'config.php') || $this->main->fs()->is_file($this->realpath_projectroot_dir.$this->path_home_dir.'config.json')){
					$status->confFileExists = true;

					// ここでは初期化だけ。configをロードしたあとでセットする
					$status->px2DTConfFileExists = false;
				}
			}

			if($this->main->fs()->is_dir($this->realpath_projectroot_dir.'/vendor/')){
				$status->vendorDirExists = true;
			}

			if( $status->entryScriptExists && $status->homeDirExists && $status->confFileExists && $status->composerJsonExists && $status->vendorDirExists ){
				$status->isPxStandby = true;
			}
	
			if($this->main->fs()->is_dir($this->realpath_projectroot_dir.'/.git/')){
				$status->gitDirExists = true;
			}
		}


		if(
			!$status->isPxStandby
		){
			// この時点で条件を満たしていなければ、
			// PXコマンドを実行できないと判断する。
			return $status;
		}

		$pjInfo = $this->get_project_info();
		// var_dump($pjInfo);

		if( is_object($pjInfo) && is_object($pjInfo->check_status) ){
			if( is_object($pjInfo->check_status->pxfw_api) ){
				$status->api->version = $pjInfo->check_status->pxfw_api->version;
				$status->api->available = ($pjInfo->check_status->pxfw_api->version ? true : false);
				$status->api->is_sitemap_loaded = $pjInfo->check_status->pxfw_api->is_sitemap_loaded;
			}
			if( is_object($pjInfo->check_status->px2dthelper) ){
				$status->px2dthelper->version = $pjInfo->check_status->px2dthelper->version;
				$status->px2dthelper->available = ($pjInfo->check_status->px2dthelper->version ? true : false);
				$status->px2dthelper->is_sitemap_loaded = $pjInfo->check_status->px2dthelper->is_sitemap_loaded;
			}
		}


		$_config = false;
		$_px2DTConfig = false;
		if( is_object($pjInfo) && is_object($pjInfo->config) ){
			$_config = $pjInfo->config;
			if( $_config->plugins && $_config->plugins->px2dt ){
				$_px2DTConfig = $_config->plugins->px2dt;
				$status->px2DTConfFileExists = true;
			}
		}

		$status->guiEngineName = 'broccoli-html-editor';

		if( $_config && $_config->plugins && $_config->plugins->px2dt && $_config->plugins->px2dt->guiEngine ){
			switch($_config->plugins->px2dt->guiEngine){
				case 'broccoli-html-editor-php':
					$status->guiEngineName = 'broccoli-html-editor-php';
					break;
				default:
					break;
			}
		}

		return $status;
	}

	/**
	 * プロジェクト情報を取得する
	 */
	public function get_project_info(){
		if( is_object($this->pjInfo) ){
			// すでに取得済みだった場合、そのときの値を返す
			return $this->pjInfo;
		}
		$pjInfo = $this->query('/?PX=px2dthelper.get.all', array(
			'output' => 'json',
		));
		$this->pjInfo = $pjInfo;
		return $this->pjInfo;
	}

	/**
	 * Pickles 2 を実行する
	 *
	 * @param string $request_path リクエストを発行する対象のパス
	 * @param array $options Pickles 2 へのコマンド発行時のオプション
	 * - output = 期待する出力形式。`json` を指定すると、コマンドに `-o json` オプションが加えられ、JSON形式で解析済みのオブジェクトが返されます。
	 * - user_agent = `HTTP_USER_AGENT` 文字列。 `user_agent` が空白の場合、または文字列 `PicklesCrawler` を含む場合には、パブリッシュツールからのアクセスであるとみなされます。
	 * @return mixed Pickles 2 の実行結果。
	 * 通常は 得られた標準出力をそのまま文字列として返します。
	 * `output` オプションに `json` が指定された場合、 `json_decode()` された値が返却されます。
	 */
	public function query($request_path, $options = null){
		if( !strlen($this->path_entry_script) ){
			$this->status();
		}
		$px2agent = new \picklesFramework2\px2agent\px2agent();
		$px2proj = $px2agent->createProject( realpath($this->realpath_projectroot_dir.$this->path_entry_script) );
		return $px2proj->query($request_path, $options);
	} // query()

}

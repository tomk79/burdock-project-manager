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
	 * Constructor
	 */
	public function __construct( $main, $realpath_projectroot_dir ){
		$this->main = $main;
		$this->realpath_projectroot_dir = $realpath_projectroot_dir;
	}

	/**
	 * 環境個別の状態チェック
	 */
	public function status(){

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

		$status->composerJsonExists = false;
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

		$status->entryScriptExists = false;
		$status->homeDirExists = false;
		$status->confFileExists = false;
		$status->px2DTConfFileExists = false;
		$status->vendorDirExists = false;
		$status->isPxStandby = false;
		$status->gitDirExists = false;

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


		$pjInfo = $this->execute_px2('/?PX=px2dthelper.get.all', array(
			'output' => 'json',
		));
		// var_dump($pjInfo);

		$status->api->version = $pjInfo->check_status->pxfw_api->version;
		$status->api->available = ($pjInfo->check_status->pxfw_api->version ? true : false);
		$status->api->is_sitemap_loaded = $pjInfo->check_status->pxfw_api->is_sitemap_loaded;

		$status->px2dthelper->version = $pjInfo->check_status->px2dthelper->version;
		$status->px2dthelper->available = ($pjInfo->check_status->px2dthelper->version ? true : false);
		$status->px2dthelper->is_sitemap_loaded = $pjInfo->check_status->px2dthelper->is_sitemap_loaded;


		$_config = $pjInfo->config;
		$_px2DTConfig = false;
		if( $_config->plugins && $_config->plugins->px2dt ){
			$_px2DTConfig = $_config->plugins->px2dt;
			$status->px2DTConfFileExists = true;
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
	 * Pickles 2 を実行する
	 *
	 * @param string $request_path リクエストを発行する対象のパス
	 * @param array $options Pickles 2 へのコマンド発行時のオプション
	 * - output = 期待する出力形式。`json` を指定すると、サブリクエストに `-o json` オプションが加えられ、JSON形式で解析済みのオブジェクトが返されます。
	 * - user_agent = `HTTP_USER_AGENT` 文字列。 `user_agent` が空白の場合、または文字列 `PicklesCrawler` を含む場合には、パブリッシュツールからのアクセスであるとみなされます。
	 * @param int &$return_var コマンドの終了コードで上書きされます
	 * @return mixed サブリクエストの実行結果。
	 * 通常は 得られた標準出力をそのまま文字列として返します。
	 * `output` オプションに `json` が指定された場合、 `json_decode()` された値が返却されます。
	 *
	 * サブリクエストから標準エラー出力を検出した場合、 `$px->error( $stderr )` に転送します。
	 */
	private function execute_px2($request_path, $options = null){
		if( !strlen($this->path_entry_script) ){
			$this->status();
		}
		$px2agent = new \picklesFramework2\px2agent\px2agent();
		$px2proj = $px2agent->createProject( realpath($this->realpath_projectroot_dir.$this->path_entry_script) );
		return $px2proj->query($request_path, $options);
	} // execute_px2()

}

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
	public function get_initializing_request(){
		$realpath_json = $this->realpath_bd_data.'projects/'.urlencode($this->project_id).'/initializing_request.json';
		if( !is_file($realpath_json) || !is_readable($realpath_json) ){
			$json = new \stdClass();
			$json->initializing_method = null;
			$json->git_remote = null;
			$json->git_user_name = null;
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
	public function save_initializing_request($json){
		if( !is_object($json) && !is_array($json) ){
			return false;
		}

		$realpath_json = $this->realpath_bd_data.'projects/'.urlencode($this->project_id).'/initializing_request.json';
		if( !is_dir( dirname($realpath_json) ) ){
			$this->main->fs()->mkdir_r(dirname($realpath_json));
		}

		$json_string = json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		$result = $this->main->fs()->save_file($realpath_json, $json_string);
		return $result;
	}

	/**
	 * アプリケーションロックする。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @param int $expire 有効時間(秒) (省略時: 60秒)
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
	 */
	public function lock( $app_name, $expire = 60 ){
		$lockfilepath = $this->realpath_bd_data.'/projects/applock/'.urlencode($app_name).'.lock.txt';
		$timeout_limit = 5;

		if( !is_dir( dirname( $lockfilepath ) ) ){
			if( !$this->main->fs()->mkdir_r( dirname( $lockfilepath ) ) ){
				return false;
			}
		}

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		$i = 0;
		while( $this->is_locked( $app_name, $expire ) ){
			$i ++;
			if( $i >= $timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			// PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
		$RTN = $this->main->fs()->save_file( $lockfilepath , $src );
		return	$RTN;
	} // lock()

	/**
	 * アプリケーションロックされているか確認する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @param int $expire 有効時間(秒) (省略時: 60秒)
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
	 */
	public function is_locked( $app_name, $expire = 60 ){
		$lockfilepath = $this->realpath_bd_data.'/projects/applock/'.urlencode($app_name).'.lock.txt';
		$lockfile_expire = $expire;

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		if( $this->main->fs()->is_file($lockfilepath) ){
			if( ( time() - filemtime($lockfilepath) ) > $lockfile_expire ){
				// 有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
	} // is_locked()

	/**
	 * アプリケーションロックを解除する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
	 */
	public function unlock( $app_name ){
		$lockfilepath = $this->realpath_bd_data.'/projects/applock/'.urlencode($app_name).'.lock.txt';

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		return @unlink( $lockfilepath );
	} // unlock()

	/**
	 * アプリケーションロックファイルの更新日を更新する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	public function touch_lockfile( $app_name ){
		$lockfilepath = $this->realpath_bd_data.'/projects/applock/'.urlencode($app_name).'.lock.txt';

		// PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !is_file( $lockfilepath ) ){
			return false;
		}

		return touch( $lockfilepath );
	} // touch_lockfile()
}

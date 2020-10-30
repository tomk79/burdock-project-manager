<?php
/**
 * Pickles 2 - Burdock Project Manager
 */
namespace tomk79\picklesFramework2\burdock\projectManager;

/**
 * Burdock Project Manager: main class
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class main{

	/** filesystem utility */
	private $fs;

	/** Initialize Config */
	private $conf;

	/** Path to Burdock Data Directory */
	private $realpath_bd_data;

	/**
	 * Constructor
	 */
	public function __construct( $realpath_bd_data, $options = array() ){
		$this->fs = new \tomk79\filesystem();
		$this->realpath_bd_data = $this->fs->get_realpath($realpath_bd_data.'/');
		if( !$this->fs->is_dir( $this->realpath_bd_data ) ){
			$this->realpath_bd_data = false;
		}

		if( !is_array( $options ) ){
			$options = (array) $options;
		}
		if( !array_key_exists('php', $options) ){
			$options['php'] = 'php';
		}
		if( !array_key_exists('php_ini', $options) ){
			$options['php_ini'] = null;
		}
		$this->conf = $options;
	}

	/**
	 * Create project object
	 */
	public function project($project_id){
		$pj = new project($this, $this->realpath_bd_data, $project_id);
		return $pj;
	}

	/**
	 * $fs
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * confs
	 */
	public function conf($key = null){
		if( is_null( $key ) ){
			return $this->conf;
		}
		return $this->conf[$key];
	}

	/**
	 * プロジェクトの一覧を取得する
	 *
	 * このメソッドが返すのは、 `<BD_DATA_DIR>/projects/` の直下にあるディレクトリ名の一覧です。
	 */
	public function get_project_list(){
		$ls = $this->fs()->ls($this->realpath_bd_data.'projects/');
		$rtn = array();
		foreach($ls as $basename){
			if( !strlen($basename) ){
				continue;
			}
			if( !is_dir($this->realpath_bd_data.'projects/'.$basename) ){
				continue;
			}
			array_push($rtn, $basename);
		}
		return $rtn;
	}

	/**
	 * アプリケーションロックする。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @param int $expire 有効時間(秒) (省略時: 60秒)
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
	 */
	public function lock( $app_name, $expire = 60 ){
		$lockfilepath = $this->realpath_bd_data.'/applock/'.urlencode($app_name).'.lock.txt';
		$timeout_limit = 5;

		if( !is_dir( dirname( $lockfilepath ) ) ){
			if( !$this->fs()->mkdir_r( dirname( $lockfilepath ) ) ){
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
		$src .= date( 'Y-m-d H:i:s' , time() )."\r\n";
		$RTN = $this->fs()->save_file( $lockfilepath , $src );
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
		$lockfilepath = $this->realpath_bd_data.'/applock/'.urlencode($app_name).'.lock.txt';
		$lockfile_expire = $expire;

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		if( $this->fs()->is_file($lockfilepath) ){
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
		$lockfilepath = $this->realpath_bd_data.'/applock/'.urlencode($app_name).'.lock.txt';

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
		$lockfilepath = $this->realpath_bd_data.'/applock/'.urlencode($app_name).'.lock.txt';

		// PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !is_file( $lockfilepath ) ){
			return false;
		}

		return touch( $lockfilepath );
	} // touch_lockfile()

}

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
}

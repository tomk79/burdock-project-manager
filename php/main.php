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

	/** Path to Burdock Data Directory */
	private $realpath_bd_data;

	/**
	 * Constructor
	 */
	public function __construct( $realpath_bd_data ){
		$this->fs = new \tomk79\filesystem();
		$this->realpath_bd_data = $this->fs->get_realpath($realpath_bd_data.'/');
		if( !$this->fs->is_dir( $this->realpath_bd_data ) ){
			$this->realpath_bd_data = false;
		}
	}

	/**
	 * Create project object
	 */
	public function pj($project_id){
		$pj = new project($this, $project_id);
		return $pj;
	}

}

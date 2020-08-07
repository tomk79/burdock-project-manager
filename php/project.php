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

}

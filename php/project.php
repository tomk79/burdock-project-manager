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

	/**
	 * Constructor
	 */
	public function __construct( $main, $project_id ){
		$this->main = $main;
		$this->project_id = $project_id;
	}

}

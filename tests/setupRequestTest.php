<?php
/**
 * Setup Request test
 */
class setupRequestTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $helper_commander;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/helper/commander.php');
		$this->helper_commander = new test_helper_commander();
	}


	/**
	 * セットアップリクエストを保存するテスト
	 */
	public function testSavingSetupRequest(){
		@unlink( __DIR__.'/testdata/bd_data_main/projects/test_pj_fine/setup_request.json' );
		clearstatcache();

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$this->assertSame( is_object($burdockProjectManager), true );

		$pj = $burdockProjectManager->project('test_pj_fine');
		$this->assertSame( is_object($pj), true );

		$setupRequest = $pj->get_setup_request();
		$this->assertSame( is_object($setupRequest), true );

		$setupRequest = new \stdClass();
		$setupRequest->git_remote = null;
		$setupRequest->git_user_name = null;
		$setupRequest->git_password = null;
		$setupRequest->composer_vendor_name = null;
		$setupRequest->composer_project_name = null;

		$result = $pj->save_setup_request($setupRequest);
		$this->assertSame( $result, true );

		return;
	}

	/**
	 * セットアップリクエストを取得するテスト
	 */
	public function testGettingSetupRequest(){

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$pj = $burdockProjectManager->project('test_pj_fine');

		$setupRequest = $pj->get_setup_request();
		$this->assertSame( is_object($setupRequest), true );

		$this->assertSame( $setupRequest->git_remote, null );
		$this->assertSame( $setupRequest->git_user_name, null );
		$this->assertSame( $setupRequest->git_password, null );
		$this->assertSame( $setupRequest->composer_vendor_name, null );
		$this->assertSame( $setupRequest->composer_project_name, null );

		return;
	}

}

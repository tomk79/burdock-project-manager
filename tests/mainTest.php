<?php
/**
 * main test
 */
class mainTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $helper_commander;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/helper/commander.php');
		$this->helper_commander = new test_helper_commander();
		$this->helper_commander->execute('testdata/bd_data_main/repositories/test_pj_fine---master/', 'composer install');
		$this->helper_commander->execute('testdata/bd_data_main/repositories/test_pj_fine---master/', 'git init');
	}


	/**
	 * インスタンス化してみるテスト
	 */
	public function testCreateNewInstance(){
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$this->assertSame( is_object($burdockProjectManager), true );

		$pj = $burdockProjectManager->project('test_pj_fine');
		$this->assertSame( is_object($pj), true );
		return;
	}

	/**
	 * アプリケーションロックのテスト
	 */
	public function testAppLock(){
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );

		$appname = 'testapp';
		$this->assertSame( $burdockProjectManager->is_locked($appname), false );
		$this->assertSame( $burdockProjectManager->lock($appname), true );
		$this->assertSame( $burdockProjectManager->lock($appname), false );
		$this->assertSame( $burdockProjectManager->is_locked($appname), true );
		$this->assertSame( $burdockProjectManager->is_locked('not_'.$appname), false );
		$this->assertSame( $burdockProjectManager->unlock($appname), true );
		$this->assertSame( $burdockProjectManager->unlock($appname), false );
		return;
	}

	/**
	 * アプリケーションロック(プロジェクト別)のテスト
	 */
	public function testProjectAppLock(){
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$pj = $burdockProjectManager->project('test_pj_fine');

		$appname = 'testapp';
		$this->assertSame( $pj->is_locked($appname), false );
		$this->assertSame( $pj->lock($appname), true );
		$this->assertSame( $pj->lock($appname), false );
		$this->assertSame( $pj->is_locked($appname), true );
		$this->assertSame( $pj->is_locked('not_'.$appname), false );
		$this->assertSame( $pj->unlock($appname), true );
		$this->assertSame( $pj->unlock($appname), false );
		return;
	}

	/**
	 * プロジェクト一覧を取得するテスト
	 */
	public function testGetProjectList(){
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$projects = $burdockProjectManager->get_project_list();

		$this->assertSame( count($projects), 1 );
		$this->assertSame( $projects[0], 'test_pj_fine' );
		return;
	}

	/**
	 * 正常にセットアップができていて、稼働できる状態のプロジェクトをチェックするテスト
	 */
	public function testAvailableBranch(){
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$pj = $burdockProjectManager->project('test_pj_fine');

		$status = $pj->branch('master', 'preview')->status();
		// var_dump($status);
		$this->assertSame( is_object($status), true );
		$this->assertSame( is_object($status->api), true );
		$this->assertSame( $status->api->available, true );
		$this->assertSame( is_string($status->api->version), true );
		$this->assertSame( $status->api->is_sitemap_loaded, true );
		$this->assertSame( is_object($status->px2dthelper), true );
		$this->assertSame( $status->px2dthelper->available, true );
		$this->assertSame( is_string($status->px2dthelper->version), true );
		$this->assertSame( $status->px2dthelper->is_sitemap_loaded, true );
		$this->assertSame( $status->pathExists, true );
		$this->assertSame( $status->pathContainsFileCount, 8 );
		$this->assertSame( $status->composerJsonExists, true );
		$this->assertSame( $status->entryScriptExists, true );
		$this->assertSame( $status->homeDirExists, true );
		$this->assertSame( $status->confFileExists, true );
		$this->assertSame( $status->px2DTConfFileExists, true );
		$this->assertSame( $status->vendorDirExists, true );
		$this->assertSame( $status->isPxStandby, true );
		$this->assertSame( $status->gitDirExists, true );
		$this->assertSame( $status->guiEngineName, "broccoli-html-editor-php" );
		return;
	}

	/**
	 * 初期化できていない空白のブランチをチェックするテスト
	 */
	public function testEmptyBranch(){
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( __DIR__.'/testdata/bd_data_main' );
		$pj = $burdockProjectManager->project('test_pj_empty');

		$status = $pj->branch('master', 'preview')->status();
		// var_dump($status);
		$this->assertSame( is_object($status), true );
		$this->assertSame( is_object($status->api), true );
		$this->assertSame( $status->api->available, false );
		$this->assertSame( $status->api->version, false );
		$this->assertSame( $status->api->is_sitemap_loaded, false );
		$this->assertSame( is_object($status->px2dthelper), true );
		$this->assertSame( $status->px2dthelper->available, false );
		$this->assertSame( $status->px2dthelper->version, false );
		$this->assertSame( $status->px2dthelper->is_sitemap_loaded, false );
		$this->assertSame( $status->pathExists, true ); // プロジェクトのルートディレクトリだけは存在している
		$this->assertSame( $status->pathContainsFileCount, 1 ); // .gitkeep がリストされるから 1件
		$this->assertSame( $status->composerJsonExists, false );
		$this->assertSame( $status->entryScriptExists, false );
		$this->assertSame( $status->homeDirExists, false );
		$this->assertSame( $status->confFileExists, false );
		$this->assertSame( $status->px2DTConfFileExists, false );
		$this->assertSame( $status->vendorDirExists, false );
		$this->assertSame( $status->isPxStandby, false );
		$this->assertSame( $status->gitDirExists, false );
		$this->assertSame( $status->guiEngineName, null );
		return;
	}

}

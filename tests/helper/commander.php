<?php
class test_helper_commander{

	/**
	 * コマンドを実行する
	 */
	public function execute($realpath, $cmd){

		$current_dir = realpath('.');
		$project_dir = dirname(__DIR__).'/'.$realpath;

		// コマンドを実行
		chdir($project_dir);
		ob_start();
		$proc = proc_open($cmd, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);
		$io = array();
		foreach($pipes as $idx=>$pipe){
			$io[$idx] = null;
			if( $idx >= 1 ){
				$io[$idx] = stream_get_contents($pipe);
			}
			fclose($pipe);
		}
		$return_var = proc_close($proc);
		ob_get_clean();

		$bin = $io[1]; // stdout
		if( strlen( $io[2] ) ){
			$bin .= $io[2];
		}

		chdir($current_dir);
		return $bin;

	}
}

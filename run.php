<?php
/**
 * Magento  Tool:Balancer languages.
 * Version: 1.0
 * Author: Roman Hinex
 * Email: roman@hinex.org
 * Site: http://hinex.org
 * GitHub: https://github.com/HiNeX/BalancerLanguagesTool
 */

class MagentoBalancerLanguages {
	/**
	 * @var array
	 */
	protected $configure = [
		'base'		=>	'./language_base/',
		'src'		=>	'./language_src/',
		'result'	=>	'./language_reuslt/'
	];
	/**
	 * @var array
	 */
	protected $base		=	[];
	/**
	 * @var array
	 */
	protected $src		=	[];

	/**
	 * Check read and write access.
	 * @param $dir
	 *
	 * @return int
	 * @throws Exception
	 */
	protected function checkDir($dir) {
		if (!is_dir($this->configure[$dir]))
			throw new Exception('Directory "'.$this->configure[$dir].'" not found.');
		
		if (!is_readable($this->configure[$dir]))
			throw new Exception('Access to directory "'.$this->configure[$dir].'" denied.');
		
		return 0;
	}

	/**
	 * Get base files.
	 * @return array
	 * @throws Exception
	 */
	protected function getBasePack() {
		$this->checkDir('base');
		$this->base = glob($this->configure['base'].'*.csv');

		if (empty($this->base))
			throw new Exception('Base directory "'.$this->configure['base'].'" is empty.');

		return $this->base;
	}

	/**
	 * Get resource files.
	 * @return array
	 * @throws Exception
	 */
	protected function getSrcPack() {
		$this->checkDir('src');

		$list = glob($this->configure['src'].'*.csv');

		if (empty($list))
			throw new Exception('Source directory "'.$this->configure['src'].'" is empty.');

		foreach ($list as $value) {
			$filename				= basename($value);
			$this->src[$filename]	= $value;
		}

		return $this->src; 
	}

	/**
	 * Parse translation file.
	 * @param $path
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function getTranslation($path) {
		if (!is_readable($path))
			throw new Exception('Access to directory "'.$path.'" denied.');

		$lang	=	array();
		$lines	=	file($path, FILE_IGNORE_NEW_LINES);

		foreach ($lines as $key => $value)
		{
			$values = str_getcsv($value);

			if (count($values) == 2) {
	 	    	$lang[md5($values[0])] = ['key' => $values[0], 'translation' => $values[1]];
			}
		}
		return $lang;
	}

	/**
	 * Generate translation file.
	 * @param $filename
	 *
	 * @return bool
	 */
	protected function generateNewFile($filename) {
		if (is_file($this->configure['src'].$filename)) {
			$base		=	$this->getTranslation($this->configure['base'].$filename);
			$src		=	$this->getTranslation($this->configure['src'].$filename);
			
			foreach ($base as $key => $value) {
				if (isset($src[$key])) {
					if ($key != md5($src[$key]['translation'])) {
						$base[$key] = $src[$key];
					}
					unset($src[$key]);
				}
			}

			foreach ($src as $key_src => $value_src) {
				$base[$key_src] = $value_src;
			}

			ksort($base);
			$file = array();
			foreach ($base as $key => $value) {
				$file[]= '"'.$value['key'].'", "'.$value['translation'].'"';
			}

			file_put_contents($this->configure['result'].$filename, implode("\n", $file));

			return true;
		}

		return false;
	}

	/**
	 * Copy not translated files.
	 */
	protected function copyNotTranslatedFiles() {
		if (!empty($this->src)) {
						$this->printConsole('', 'text', 1);
			$this->printConsole('Copy not translated files: ', 'title', 0);
			foreach ($this->src as $id => $value) {
				$this->printConsole('', 'text', 1);
				$filename	=	basename($value);

				$this->printConsole('File: ', 'info');
				$this->printConsole('"'.$filename.'"...', 'text');

				$src		=	$this->configure['src'].$filename;
				$result		=	$this->configure['result'].$filename;

				copy($src, $result);
				$this->printConsole(' ok', 'success');
			}
		}
	}

	/**
	 * Start progress.
	 */
	protected function startProgress() {
		$this->checkDir('result');

		$base	=	$this->getBasePack();
		$this->getSrcPack();

		$this->printConsole('Progress: ', 'title', 1);

		foreach ($base as $id => $value) {
			$filename	=	basename($value);
			$src		=	$this->configure['src'].$filename;

			$this->printConsole('File: ', 'info');
			$this->printConsole('"'.$filename.'"...', 'text');

			unset($this->src[$filename]);

			if ($this->generateNewFile($filename)) {
				$this->printConsole(' ok', 'success');
			} else {
				$this->printConsole(' skip', 'warning');
			}

			$this->printConsole('', 'text', 1);
		}
	}

	/**
	 * Show colored messages.
	 * @param        $print
	 * @param string $type
	 * @param int    $break
	 */
	protected function printConsole($print, $type='text', $break = 0) {
		switch ($type) {
			case 'info':
				echo "\033[36m{$print}";
				break;

			case 'success':
				echo "\033[32m{$print}";
				break;

			case 'warning':
				echo "\033[37m{$print}";
				break;

			case 'error':
				echo "\033[31m{$print}";
				break;
			
			case 'title':
				echo "\033[1m\033[36m{$print}\033[0m";
				break;

			default:
				echo "\033[0m{$print}";
				break;
		}

		for ($i=0; $i<$break; $i++) echo "\n";
	}

	/**
	 * Execute all commands.
	 */
	public function run() {
		$this->printConsole('Magento  Tool: Balancer languages [v. 1.0]', 'title', 2);
		$this->printConsole('Author:  Roman Hinex', 'text', 1);
		$this->printConsole('E-Mail:  roman@hinex.org', 'text', 1);
		$this->printConsole('Site:    http://hinex.org', 'text', 1);
		$this->printConsole('Project: https://github.com/HiNeX/BalancerLanguagesTool', 'text', 2);

		try {
			$this->startProgress();
			$this->copyNotTranslatedFiles();
		} catch (Exception $e) {
		    $this->printConsole('Error: '. $e->getMessage(), 'error', 1);
		}
	}
}

$class = new MagentoBalancerLanguages();
$class->run();
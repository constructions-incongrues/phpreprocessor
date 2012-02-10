<?php
namespace Com\AramisAuto;

class PHPreprocessor
{
	/**
	 * @var SplFileObject
	 */
	private $_log;

	public function __construct(\SplFileObject $logfile)
	{
		$this->_log = $logfile;
	}

	/**
	 * Extracts tokens from files in source directory and outputs corresponding stub properties file to stdout.
	 */
	public function extract(array $options)
	{
		// TODO : options sanity checks

		// Find dist files
		$files = $this->_findDistFiles($options['src']);

		// Extract tokens
		$tokens = $this->_extractTokensFromFiles($files);

		// Output results
		$file = new \SplFileObject('php://stdout', 'w');
		$file->fwrite($this->_generateProperties($tokens));
	}

	public function apply(array $options)
	{
		// TODO : options sanity checks

		// Find dist files
		$files = $this->_findDistFiles($options['src']);

		// Unserialize tokens
		$tokensFiles = explode(',', $options['properties']);
		$tokens = array();
		foreach ($tokensFiles as $tokenFile)
		{
			if (!is_readable($tokenFile))
			{
				throw new RuntimeException(sprintf('File "%s" is not readable', $tokenFile));
			}
			$tokens = array_merge($tokens, parse_ini_file($tokenFile));
		}

		// Create non -dist files
		$copied_files = array();
		foreach ($files as $file)
		{
			$new_file = substr($file, 0, strlen($file) - strlen('-dist'));
			copy($file, $new_file); // TODO : sanity checks
			$copied_files[] = $new_file;
		}

		// Perform replacements
		$this->_replaceTokens($copied_files, '@', '@', $tokens);
	}

	/**
	 * Finds *-dist files in given directory.
	 *
	 * @param string $srcDir Path to sources root directory
	 *
	 * @return array An array of paths to found -dist files
	 */
	private function _findDistFiles($srcDir)
	{
		$distFiles = array();
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($srcDir, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS));
		foreach($iterator as $path => $fileInfo) {
			if ($fileInfo->isFile()) {
				if (preg_match('/.*-dist$/', $fileInfo->getFilename())) {
					$distFiles[] = $path;
				}
			}
		}

		$this->_logMessage('Found %d -dist files in directory "%s"', array(count($distFiles), $srcDir));

		return $distFiles;
	}

	/**
	 * Extracts, deduplicates and sort tokens by namespace.
	 *
	 * @param array $files
	 *
	 * @return array $tokens
	 */
	private function _extractTokensFromFiles(array $files)
	{
		// Extract tokens from files
		$tokens = array();
		foreach ($files as $path) {
			$matches = array();
			preg_match_all('/@[a-z0-9\._]*?@/i', file_get_contents($path), $matches);
			if (count($matches[0])) {
				foreach ($matches[0] as $token) {
					if (!isset($tokens[$token])) {
						$tokens[$token] = array($path);
					} else {
						$tokens[$token][] = $path;
					}
				}
			}
		}

		// Cleanup
		foreach ($tokens as $token => $files) {
			$tokens[trim($token, '@')] = array_unique($files);
			unset($tokens[$token]);
		}
		ksort($tokens);

		// Create namespaces
		$tokensNamespaces = array();
		foreach ($tokens as $token => $files) {
			$parts = explode('.', $token);
			$namespace = $parts[0];
			if (!isset($tokensNamespaces[$namespace])) {
				$tokensNamespaces[$namespace] = array();
			}
			$tokensNamespaces[$namespace][$token] = $files;
		}
		ksort($tokensNamespaces);

		return $tokensNamespaces;
	}

	private function _generateProperties(array $tokensNamespaces)
	{
		// Generate properties file text
		$lines = array();

		// File header
		$lines[] = sprintf('# This file has been automatically generated by %s', __CLASS__);
		$lines[] = '';

		foreach ($tokensNamespaces as $namespace => $tokens) {
			$lines[] = sprintf('# %s', $namespace);
			foreach ($tokens as $token => $files) {
				$lines[] = sprintf('%s=', $token);
			}
			$lines[] = '';
		}

		return implode("\n", $lines);
	}

	/**
	 * Replaces tokens in an array of files.
	 *
	 * @param array  $files       An array of filenames
	 * @param string $beginToken  The begin token delimiter
	 * @param string $endToken    The end token delimiter
	 * @param array  $tokens      An array of token/value pairs
	 */
	private function _replaceTokens($files, $beginToken, $endToken, $tokens)
	{
		if (!is_array($files)) {
			$files = array($files);
		}

		foreach ($files as $file) {
			$content = file_get_contents($file);
			foreach ($tokens as $key => $value)	{
				$content = str_replace($beginToken.$key.$endToken, $value, $content, $count);
			}

			file_put_contents($file, $content);
		}

		$this->_logMessage('Replaced %d tokens in %d files', array(count($tokens), count($files)));
	}

	private function _logMessage($messagePattern, array $values = array())
	{
		$messagePattern = sprintf('[%s] %s', date('r'), $messagePattern);
		array_unshift($values, $messagePattern);
		$message = call_user_func_array('sprintf', $values);
		$this->_log->fwrite($message."\n");
	}
}

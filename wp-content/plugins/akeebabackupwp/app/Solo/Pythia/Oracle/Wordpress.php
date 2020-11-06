<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Pythia\Oracle;

use Solo\Pythia\AbstractOracle;

class Wordpress extends AbstractOracle
{
	/**
	 * The name of this oracle class
	 *
	 * @var  string
	 */
	protected $oracleName = 'wordpress';

	/**
	 * Does this class recognises the CMS type as Wordpress?
	 *
	 * @return  boolean
	 */
	public function isRecognised()
	{
		if (!@file_exists($this->path . '/wp-config.php') && !@file_exists($this->path . '/../wp-config.php'))
		{
			return false;
		}

		if (!@file_exists($this->path . '/wp-login.php'))
		{
			return false;
		}

		if (!@file_exists($this->path . '/xmlrpc.php'))
		{
			return false;
		}

		if (!@is_dir($this->path . '/wp-admin'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Return the database connection information for this CMS / script
	 *
	 * @return  array
	 */
	public function getDbInformation()
	{
		$ret = array(
			'driver'	=> 'mysqli',
			'host'		=> '',
			'port'		=> '',
			'username'	=> '',
			'password'	=> '',
			'name'		=> '',
			'prefix'	=> '',
		);

		$filePath = $this->path . '/wp-config.php';

		if (!@file_exists($filePath))
		{
			$filePath = $this->path . '/../wp-config.php';
		}

		$hasTokenizer = function_exists('token_get_all');
		$fileContents = file_get_contents($filePath);

		if ($hasTokenizer)
		{
			$newValues = $this->parseWithTokenizer($fileContents);
		}
		else
		{
			$newValues = $this->parseWithoutTokenizer($fileContents);
		}

		return array_merge($ret, $newValues);
	}

	/**
	 * Parse the wp-config.php file using the PHP tokenizer extension. We use the tokenizer to remove all comments, then
	 * our regular code to parse the resulting file. Profit!
	 *
	 * @param   string $fileContents The contents of the file
	 *
	 * @return  array
	 */
	protected function parseWithTokenizer($fileContents)
	{
		$tokens = token_get_all($fileContents);

		$commentTokens = [T_COMMENT];

		if (defined('T_DOC_COMMENT'))
		{
			$commentTokens[] = T_DOC_COMMENT;
		}

		if (defined('T_ML_COMMENT'))
		{
			$commentTokens[] = T_ML_COMMENT;
		}

		$newStr  = '';

		foreach ($tokens as $token)
		{
			if (is_array($token))
			{
				if (in_array($token[0], $commentTokens))
				{
					/**
					 * If the comment ended in a newline we need to output the newline. Otherwise we will have
					 * run-together lines which won't be parsed correctly by parseWithoutTokenizer.
					 */
					if (substr($token[1], -1) == "\n")
					{
						$newStr .= "\n";
					}

					continue;
				}

				$token = $token[1];
			}

			$newStr .= $token;
		}

		return $this->parseWithoutTokenizer($newStr);
	}

	/**
	 * Parse the wp-config.php file without using the PHP tokenizer extension
	 *
	 * @param   string $fileContents The contents of the wp-config.php file
	 *
	 * @return  array
	 */
	protected function parseWithoutTokenizer($fileContents)
	{
		$fileContents = explode("\n", $fileContents);
		$fileContents = array_map('trim', $fileContents);
		$ret          = [];

		foreach ($fileContents as $line)
		{
			$line = trim($line);

			if (strpos($line, 'define') !== false)
			{
				list ($key, $value) = $this->parseDefine($line);

				switch (strtoupper($key))
				{
					case 'DB_NAME':
						$ret['name'] = $value;
						break;

					case 'DB_USER':
						$ret['username'] = $value;
						break;

					case 'DB_PASSWORD':
						$ret['password'] = $value;
						break;

					case 'DB_HOST':
						$ret['host'] = $value;
						break;

					case 'DB_CHARSET':
						$ret['charset'] = $value;
						break;

					case 'DB_COLLATE':
						$ret['collate'] = $value;
						break;

				}
			}
			elseif (strpos($line, '$table_prefix') === 0)
			{
				$parts         = explode('=', $line, 2);
				$prefixData    = trim($parts[1]);
				$ret['prefix'] = $this->parseStringDefinition($prefixData);
			}
		}

		return $ret;
	}
}

<?php
/**
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class SWJProjectsHelperKeys
{
	/**
	 * Key checks results.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	protected static $checkResults = array();

	/**
	 * Method to check key.
	 *
	 * @param   integer  $project_id  The id of the project.
	 * @param   string   $key         The key value.
	 *
	 * @return boolean|integer Key id on on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function checkKey($project_id = null, $key = null)
	{
		if (empty($key)) return false;

		$hash = (!empty($project_id)) ? $project_id : -1;
		$hash .= $key;
		$hash = md5($hash);
		if (!isset(self::$checkResults[$hash]))
		{
			$length = strlen($key);
			if ($length < 8)
			{
				// Check min length
				$result = false;
			}
			elseif ($length === 128)
			{    // Check master key
				$masterKey = ComponentHelper::getParams('com_swjprojects')->get('key_master');

				$result = ($key === $masterKey);
			}
			else
			{
				// Check database
				$db = Factory::getDbo();

				// Define null and now dates
				$nullDate = $db->quote($db->getNullDate());
				$nowDate  = $db->quote(JFactory::getDate()->toSql());

				// Define  projects
				$projects = array($db->quote(-1));
				if (!empty($project_id))
				{
					$projects[] = $project_id;
				}
				$projects = implode(',', $projects);

				// Build query
				$query  = $db->getQuery(true)
					->select(array('id'))
					->from($db->quoteName('#__swjprojects_keys'))
					->where($db->quoteName('project_id') . ' IN (' . $projects . ')')
					->where($db->quoteName('key') . ' = ' . $db->quote($key))
					->where('state = 1')
					->where('(date_start = ' . $nullDate . ' OR date_start <= ' . $nowDate . ')')
					->where('(date_end = ' . $nullDate . ' OR date_end >= ' . $nowDate . ')')
					->where('(' . $db->quoteName('limit') . ' = 0 OR limit_count > 0)');
				$result = (int) $db->setQuery($query)->loadResult();
			}

			self::$checkResults[$hash] = $result;
		}

		return self::$checkResults[$hash];
	}
}
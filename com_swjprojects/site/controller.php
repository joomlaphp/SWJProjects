<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class SWJProjectsController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $default_view = 'projects';

	/**
	 * Typical view method for MVC based architecture.
	 *
	 * @param  boolean $cachable  If true, the view output will be cached
	 * @param  array   $urlparams An array of safe URL parameters and their variable types.
	 *
	 * @throws  Exception
	 *
	 * @return  BaseController  A BaseController object to support chaining.
	 *
	 * @since  1.0.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view       = $this->input->get('view', $this->default_view);
		$id         = $this->input->get('id', 0, 'raw');
		$catid      = $this->input->get('catid', 1, 'raw');
		$project_id = $this->input->get('project_id', 0, 'raw');
		$element    = $this->input->get('element', '', 'raw');
		$link       = false;

		if ($view == 'version')
		{
			$link = SWJProjectsHelperRoute::getVersionRoute($id, $project_id, $catid);
		}

		if ($view == 'versions')
		{
			$link = SWJProjectsHelperRoute::getVersionsRoute($id, $catid);
		}

		if ($view == 'project')
		{

			$link = SWJProjectsHelperRoute::getProjectRoute($id, $catid);
		}

		if ($view == 'projects')
		{
			$link = SWJProjectsHelperRoute::getProjectsRoute($id);
		}

		if ($view == 'jupdate')
		{
			$link = SWJProjectsHelperRoute::getJUpdateRoute($project_id, $element);
		}

		if ($link)
		{
			$uri       = Uri::getInstance();
			$root      = $uri->toString(array('scheme', 'host', 'port'));
			$canonical = Uri::getInstance(Route::_($link))->toString();
			$current   = $uri->toString(array('path', 'query', 'fragment'));

			if ($current !== $canonical)
			{
				Factory::getDocument()->addCustomTag('<link href="' . $root . $canonical . '" rel="canonical"/>');

				$redirect = Uri::getInstance(Route::_($link));
				if (!empty($uri->getVar('start')))
				{
					$redirect->setVar('start', $uri->getVar('start'));
				}
				if (!empty($uri->getVar('debug')))
				{
					$redirect->setVar('debug', $uri->getVar('debug'));
				}

				if ($current != $redirect)
				{
					Factory::getApplication()->redirect($redirect, 301);
				}
			}
		}

		return parent::display($cachable, $urlparams);
	}

	/**
	 * Method to download version file.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function download()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel('Download');

		// Get file data
		if (!$download = $model->getFile())
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_FILE_NOT_FOUND'), 404);
		}

		// Set headers
		ob_end_clean();
		$app->clearHeaders();
		$app->setHeader('Content-Type', 'application/octet-stream', true);
		$app->setHeader('Content-Disposition', 'attachment; filename=' . $download->name . ';', true);
		$app->sendHeaders();

		// Read file
		if ($context = @file_get_contents($download->path))
		{
			echo $context;
			$model->setDownload();
		}

		// Close application
		$app->close();
	}
}
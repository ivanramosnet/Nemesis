<?php
/**
 * Nemesis plugin
 *
 * Image gallery plugin to use with H5BPTB joomla template
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.nemesis
 * @author      Iván Ramos <info@ivan.ramos.name>
 * @copyright   Copyright (C) 2012 Iván Ramos Jiménez. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Nemesis Content Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.nemsis
 * @since       1.0
 */
class plgContentNemesis extends JPlugin
{

	/**
	 * Plugin that create an image gallery within content
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, 'nemesis') === false)
		{
			return true;
		}

		// Expression to search for(galleries)
		$regexmod	= '/{nemesis\s+(.*?)}/i';
		$columns	= $this->params->get('columns', '4');

		// Find all instances of plugin and put in $matchesmod for create gallery
		preg_match_all($regexmod, $article->text, $matches, PREG_SET_ORDER);
		// If no matches, skip this
		if ($matches){
			foreach ($matches as $match) {

				$matcheslist = explode(':', $match[1]);
				// We may not have a number of columns so fall back to the plugin default.
				if (!array_key_exists(1, $matcheslist)) {
					$matcheslist[1] = $columns;
				}

				$gallery = trim($matcheslist[0]);
				$ncolumns   = trim($matcheslist[1]);
				// $match[0] is full pattern match, $match[1] is the root folder for gallery,$match[2] is the number of columns
				$output = $this->_loadgallery($gallery, $ncolumns);
				// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
			}
		}
	}

	protected function _loadgallery($gallery, $ncolumns)
	{
		// API
		jimport('joomla.filesystem.folder');

		// Path assignment
		$sitePath = JPATH_SITE . '/';
		$siteUrl  = JURI::root(true) . '/';
		
		//cargar parámetros por defecto
		
		$rootfolder 	= ($this->params->get('rootfolder')) ? $this->params->get('rootfolder') : $this->params->get('rootfolder','images'); 
		
		// Check if the source folder exists and read it
		$srcFolder = JFolder::files($sitePath.$rootfolder.'/'.$gallery);
		
		// Proceed if the folder is OK or fail silently
		if(!$srcFolder) return;
		
		// Loop through the source folder for images
		$fileTypes = array('jpg','jpeg','gif','png'); // Create an array of file types
		$found = array(); // Create an array for matching files
		foreach($srcFolder as $srcImage){
			$fileInfo = pathinfo($srcImage);
			if(array_key_exists('extension', $fileInfo) && in_array(strtolower($fileInfo['extension']),$fileTypes)){
				$found[] = $srcImage;
			}
		}
		
		// Bail out if there are no images found
		if(count($found)==0) return;
		
		$span = 'span'.(12/$ncolumns);
		
		$output = '<ul class="thumbnails">';
		
		// Loop through the image file list
		foreach($found as $key=>$filename)
		{
			
			$output .= '<li class="'.$span.'"><a class="thumbnail" href="'.$rootfolder.'/'.$gallery.'/'.$filename.'"> <img src="'.$rootfolder.'/'.$gallery.'/'.$filename.'"> </a></li>';
		}
		
		$output .= '</ul>';
		
		return $output;
	}
}

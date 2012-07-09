<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewTimeline extends JView
{

	function display($tmpl = 'default')
	{
		$srcs = FabrikHelperHTML::framework();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel();
		
		// Needed to load the language file!
		$pluginManager = FabrikWorker::getPluginManager();
		$plugin = $pluginManager->getPlugIn('timeline', 'visualization');
		
		$id = JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0)));
		$model->setId($id);
		$row = $model->getVisualization();
		$model->setListIds();

		$js = $model->render();
		$this->assign('containerId', $this->get('ContainerId'));
		$this->assignRef('row', $row);
		$this->assign('showFilters', JRequest::getInt('showfilters', 1) === 1 ?  1 : 0);
		$this->assignRef('filters', $this->get('Filters'));
		$this->assign('filterFormURL', $this->get('FilterFormURL'));
		$params = $model->getParams();
		$this->assignRef('params', $params);
		$this->width = $params->get('timeline_width', '700');
		$this->height = $params->get('timeline_height', '300');
		$tmpl = $params->get('timeline_layout', $tmpl);
		$tmplpath = '/plugins/fabrik_visualization/timeline/views/timeline/tmpl/' . $tmpl;
		$this->_setPath('template', JPATH_ROOT . $tmplpath);

		JHTML::stylesheet('media/com_fabrik/css/list.css');
		
		FabrikHelperHTML::stylesheetFromPath($tmplpath . '/template.css');
		$srcs[] = 'media/com_fabrik/js/list.js';
		$srcs[] = 'plugins/fabrik_visualization/timeline/timeline.js';
		FabrikHelperHTML::script($srcs, $js);

		$img = FabrikHelperHTML::image('calendar.png', 'form', @$this->tmpl, array('alt' => 'calendar', 'class' => 'calendarbutton', 'id' => 'timelineDatePicker_cal_img'));
		$this->datePicker = '<input type="text" name="timelineDatePicker" id="timelineDatePicker" value="" />' . $img;
		
		// Check and add a general fabrik custom css file overrides template css and generic table css
		FabrikHelperHTML::stylesheetFromPath('media/com_fabrik/css/custom.css');

		// Check and add a specific biz  template css file overrides template css generic table css and generic custom css
		FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/timeline/views/timeline/tmpl/' . $tmpl . '/custom.css');
		return parent::display();
	}
}

<?php
/**
 * Fabrik List Template: AdminModule Headings
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;
?>
<tr class="fabrik___heading">
<?php foreach ($this->headings as $key=>$heading) {
	$filterFound = array_key_exists($key, $this->filters);
	?>
	<th class="<?php echo $this->headingClass[$key]['class']?>" style="<?php echo $this->headingClass[$key]['style']?>">

		<?php

			echo '<span class="heading">' . $heading . '</span>';
		?>
		<?php if ($filterFound) {
				$filter = $this->filters[$key];
				$required = $filter->required == 1 ? ' notempty' : '';
				echo '<div class="filter '.$required.'">
				<span>'.$filter->element.'</span></div>';
		}?>
	</th>
	<?php }?>
</tr>
<?php $doc = JFactory::getDocument();
$doc->addScript(JURI::root(true).'/media/com_fabrik/js/filtertoggle.js');
$doc->addScriptDeclaration("
window.addEvent('fabrik.loaded', function() {
	new FabFilterToggle('".$this->list->renderid."');
});
"); ?>

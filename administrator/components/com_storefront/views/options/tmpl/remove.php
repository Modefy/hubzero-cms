<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JToolBarHelper::title(JText::_('COM_STOREFRONT') . ': Delete SKU', 'kb.png');
JToolBarHelper::cancel();

?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}
}
</script>
<form action="<?php echo JRoute::_('index.php?option=' . $this->option  . '&controller=' . $this->controller . '&task=' . $this->task . '&step=2'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th><?php echo JText::_('Are you sure you want to delete all selected options?'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<input type="checkbox" name="delete" value="delete"
						   id="field-delete">
					<label for="field-delete">I'm positive. Go ahead and do the delete.</label>
				</td>
			</tr>
			<tr>
				<td><input type="submit" name="Submit" value="<?php echo JText::_('COM_STOREFRONT_NEXT'); ?>" /></td>
			</tr>
		</tbody>
	</table>

	<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
	<?php
		foreach ($this->oId as $oId)
		{
			echo '<input type="hidden" name="oId[]" value="' . $oId . '" />';
		}
	?>
	<input type="hidden" name="ogId" value="<?php echo $this->ogId; ?>">
	<input type="hidden" name="option" value="<?php echo $this->option; ?>">
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">

	<?php echo JHTML::_('form.token'); ?>
</form>
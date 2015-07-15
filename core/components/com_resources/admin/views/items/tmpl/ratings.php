<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// No direct access.
defined('_HZEXEC_') or die();

if ($this->getError()) { ?>
	<p><?php echo $this->getError(); ?></p>
<?php } else { ?>
	<table class="adminform">
		<thead>
			<tr>
				<th colspan="3"><?php echo Lang::txt('COM_RESOURCES_RATINGS_TITLE'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($this->rows as $row)
		{
			if (intval($row->created) <> 0)
			{
				$thedate = Date::of($row->created)->toLocal();
			}
			$user = User::getInstance($row->user_id);
		?>
			<tr>
				<th><?php echo Lang::txt('COM_RESOURCES_RATING_USER'); ?>:</th>
				<td><?php echo $this->escape($user->get('name')); ?></td>
			</tr>
			<tr>
				<th><?php echo Lang::txt('COM_RESOURCES_RATING_VALUE'); ?>:</th>
				<td><?php echo \Components\Resources\Helpers\Html::writeRating($row->rating); ?></td>
			</tr>
			<tr>
				<th><?php echo Lang::txt('COM_RESOURCES_RATING_CREATED'); ?>:</th>
				<td><?php echo $thedate; ?></td>
			</tr>
			<tr>
				<th><?php echo Lang::txt('COM_RESOURCES_RATING_COMMENT'); ?>:</th>
				<td class="aLeft"><?php
					if ($row->comment) {
						echo $this->escape(stripslashes($row->comment));
					} else {
						echo Lang::txt('COM_RESOURCES_NONE');
					}
					?></td>
				</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>
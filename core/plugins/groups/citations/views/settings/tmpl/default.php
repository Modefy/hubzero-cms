<?php

/**
 * HUBzero CMS
 *
 * Copyright 2005-2014 Purdue University. All rights reserved.
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
 * @author    Shawn Rice <zooley@purdue.edu>, Kevin Wojkovich <kevinw@purdue.edu>
 * @copyright Copyright 2005-2014 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// No direct access
//defined('_HZEXEC_') or die();

$this->css()->js();

$base = 'index.php?option=com_groups&cn=' . $this->group->get('cn') . '&active=citations';
?>

<script type="text/javascript">
var $jQ = jQuery.noConflict();

$jQ(document).ready(function(e) {
	var formatSelector = $jQ('#format-selector'),
		formatBox = $jQ('#format-string');

	//when we change format box
	formatSelector.on('change', function(event) {
		var value  = $jQ(this).val(),
			format = $jQ(this).find(':selected').attr('data-format');
		formatBox.val(format);
	});

	//when we customize the format
	formatBox.on('keyup', function(event) {
		var customOption = formatSelector.find('option[value=custom]');
		customOption.attr('data-format', formatBox.val());
	});

	// Thanks Zach Weidner!
	$jQ(function($) {
		$('tr').click(function() {
			if ( $('select[name="format"]').find('option[value="custom"]').attr("selected") != "selected" )
			{
				// force custom format
				$('select[name="format"]').find('option[value="custom"]').attr("selected",true);

				// clear out the textarea
				$('#format-string').val('');
			}

			$('#format-string').val($('#format-string').val() + $(this).attr('id'));
			$('#format-string').focus();
		});
	});
});
</script>

<div id="browsebox" class="frm">
	<h3><?php echo Lang::txt('PLG_GROUPS_CITATIONS_SETTINGS'); ?></h3>
	<?php if ($this->getError()) { ?>
		<p class="error"><?php echo $this->getError(); ?></p>
	<?php } ?>
		<form action="<?php echo Route::url($base . '?action=settings'); ?>" method="post" id="hubForm" class="add-citation">

			<!-- Citation sources -->
			<div class="explaination">
			<p>
				Choose where the citations are being sourced from.
			</p>
			</div>

			<fieldset>
				<legend><?php echo Lang::txt('Sources'); ?></legend>

				<div class="grid">
					<div class="col span6">
						<label for="display-members">
						<?php echo Lang::txt('Select which sources of citations to display.'); ?>
						<select name="display" id="display-sources">
							<option value="group"><?php echo Lang::txt('Display group-attributed citations only.'); ?></option>
							<option value="member"><?php echo Lang::txt('Display group-attributed and group member-attributed citations.'); ?></option>
						</select>
						</label>
					</div>
					<div class="col span6 omega">
					<p id="applicableFields"><?php //echo Lang::txt('PLG_GROUPS_CITATIONS_SETTINGS'); ?>
						Group-attributed citations are those that are explicitly uploaded to the group.
					</p>
					<p>
						Member-attributed citations are those that belong to members of the group who have uploaded their own citations.
					</p>
					</div>
				</div>
			</fieldset>
			<div class="clear"></div>

			<div class="explaination">
				<p><?php //echo Lang::txt('PLG_GROUPS_CITATIONS_SETTINGS'); ?>
					<!-- Set group-level options for the display and presentation of citation information. -->
					The bibilographic style for presenting citations within this group.
				</p>
			</div>
			<fieldset>
				<legend><?php echo Lang::txt('Citation Format'); ?></legend>

				<div class="grid">
					<div class="col span7">
						<label for="cite">
							<?php echo Lang::txt('Citation Format'); ?>:
								<select name="format" id="format-selector">
									<option value="custom" data-format="">Custom for Group</option>
									<?php foreach ($this->formats as $format): ?>
										<option <?php if ($this->currentFormat->id == $format->id) { echo 'selected'; } ?> value="<?php echo $format->id; ?>" data-format="<?php echo $format->format; ?>">
											<?php echo $format->style; ?>
										</option>
									<?php endforeach; ?>
								</select>

							<span class="hint"><?php echo Lang::txt('PLG_GROUPS_CITATIONS_CITE_KEY_EXPLANATION'); ?></span>
						</label>

						<!-- some space -->
						<div class="clear"></div>

						<label for="cite">
						<textarea name="template" rows="10" id="format-string"><?php echo $this->currentFormat->format; ?></textarea>

					</div>
					<div class="col span4 omega">
						<span id="templateExplaination"> Click the key to insert into format. </span>
						<table class="templateTable">
							<thead>
								<tr class="clickable">
									<th><?php echo Lang::txt('Key'); ?></th>
									<th><?php echo Lang::txt('Value'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
									// get the keys
									foreach ($this->templateKeys as $k => $v)
									{
										echo "<tr id='{$v}'><td>{$v}</td><td>{$k}</td></tr>";
									}
								?>
							</tbody>
					</table>
					</div>
				</div>
			</fieldset><div class="clear"></div>

		<!-- submit -->
		<p class="submit">
			<input class="btn btn-success" type="submit" name="create" value="<?php echo Lang::txt('PLG_GROUPS_CITATIONS_SAVE'); ?>" />
		</p>

		<div class="clear"></div>
	</form>
</div>
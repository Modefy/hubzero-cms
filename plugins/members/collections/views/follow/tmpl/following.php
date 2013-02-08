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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$database = JFactory::getDBO();
$this->juser = JFactory::getUser();

$base = 'index.php?option=' . $this->option . '&id=' . $this->member->get('uidNumber') . '&active=' . $this->name;
?>

<form method="get" action="<?php echo JRoute::_($base . '&task=following'); ?>" id="collections">

	<fieldset class="filters">
		<ul>
<?php if ($this->params->get('access-manage-collection')) { ?>
			<li>
				<a class="livefeed tooltips" href="<?php echo JRoute::_($base); ?>" title="<?php echo JText::_('Live feed :: View posts from everything you\'re following'); ?>">
					<span><?php echo JText::_('Feed'); ?></span>
				</a>
			</li>
<?php } ?>
			<li>
				<a class="collections count" href="<?php echo JRoute::_($base . '&task=all'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> collections', $this->collections); ?></span>
				</a>
			</li>
			<li>
				<a class="posts count" href="<?php echo JRoute::_($base . '&task=posts'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> posts', $this->posts); ?></span>
				</a>
			</li>
			<li>
				<a class="followers count" href="<?php echo JRoute::_($base . '&task=followers'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> followers', $this->followers); ?></span>
				</a>
			</li>
			<li>
				<a class="following count active" href="<?php echo JRoute::_($base . '&task=following'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> following', $this->rows->total()); ?></span>
				</a>
			</li>
		</ul>
		<div class="clear"></div>
	</fieldset>

	<?php if ($this->rows->total() > 0) { ?>
		<div class="container">
			<table class="following entries" summary="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_TBL_SUMMARY'); ?>">
				<!-- <caption>
					<?php echo JText::_('People and collections you are following'); ?>
				</caption> -->
				<tbody>
		<?php foreach ($this->rows as $row) { ?>
					<tr class="<?php echo $row->get('following_type'); ?>">
						<th>
						<?php if ($row->following()->image()) { ?>
							<img src="<?php echo $row->following()->image(); ?>" width="40" height="40" alt="Profile picture of <?php echo $this->escape(stripslashes($row->following()->title())); ?>" />
						<?php } else { ?>
							<span class="entry-id">
								<?php echo $row->get('following_id'); ?>
							</span>
						<?php } ?>
						</th>
						<td>
							<a class="entry-title" href="<?php echo JRoute::_($row->following()->link()); ?>">
								<?php echo $this->escape(stripslashes($row->following()->title())); ?>
							</a><br />
							<span class="entry-details">
								<span class="follower count"><?php echo JText::sprintf('<strong>%s</strong> followers', $row->count('followers')); ?></span>
								<span class="following count"><?php echo JText::sprintf('<strong>%s</strong> following', $row->count('following')); ?></span>
							</span>
						</td>
					<?php if ($this->params->get('access-manage-collection')) { ?>
						<td>
							<a class="unfollow btn" data-id="<?php echo $row->get('following_id'); ?>" data-text-follow="<?php echo JText::_('Follow'); ?>" data-text-unfollow="<?php echo JText::_('Unfollow'); ?>" href="<?php echo JRoute::_($row->following()->link('unfollow')); ?>">
								<span><?php echo JText::_('Unfollow'); ?></span>
							</a>
						</td>
					<?php } ?>
					</tr>
		<?php } ?>
				</tbody>
			</table>
			<?php echo $this->pageNav->getListFooter(); ?>
			<div class="clear"></div>
		</div><!-- / .container -->
	<?php } else { ?>
			<div id="collection-introduction">
				<div class="instructions">
		<?php if ($this->params->get('access-manage-collection')) { ?>
					<!-- <p>
						<?php echo JText::_('You are not following anyone or any collections.'); ?>
					</p> -->
					<ol>
						<li><?php echo JText::_('Find a member or collection you like.'); ?></li>
						<li><?php echo JText::_('Click on the "follow" button.'); ?></li>
						<li><?php echo JText::_('Come back to collections and see all the posts!'); ?></li>
					</ol>
				</div><!-- / .instructions -->
				<div class="questions">
					<p><strong>What is following?</strong></p>
					<p>"Following" someone means you'll see that person's posts on this page in real time. If he/she creates a new collection, you’ll automatically follow the new collection as well.<p>
					<p>You can follow individual collections if you're only interested in seeing posts being added to specific collections.<p>
					<p>You can unfollow other people or collections at any time.</p>
				</div>
		<?php } else { ?>
					<p>
						<?php echo JText::_('This member is not following anyone or any collections.'); ?>
					</p>
				</div><!-- / .instructions -->
		<?php } ?>
			</div><!-- / #collection-introduction -->
	<?php } ?>
	<div class="clear"></div>
</form>
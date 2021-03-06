<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

Toolbar::title(Lang::txt('Search: Overview'));
Toolbar::preferences($this->option, '550');
$this->css('solr');
$option = $this->option;

\Submenu::addEntry(
	Lang::txt('Overview'),
	'index.php?option='.$option.'&task=configure'
);
\Submenu::addEntry(
	Lang::txt('Search Index'),
	'index.php?option='.$option.'&task=searchindex'
);
\Submenu::addEntry(
	Lang::txt('Index Blacklist'),
	'index.php?option='.$option.'&task=manageBlacklist'
);
?>
<div class="widget code">
	<div class="inner">
		<div class="title"><div>Solr Status</div></div>
		<div class="sub-title"><div class="sub-title-inner">Last Document Insert: <?php echo $this->lastInsert; ?></div></div>
		<div class="sub-title"><div class="sub-title-inner">Mechanism: <?php echo ucfirst($this->mechanism); ?></div> </div>
		<div class="content">
			<div class="content-inner">
				<div class="status">
					<?php if ($this->status) : ?>
						<div class="status-message">
							<div class="good"></div>
								<p>The search engine is responding.</p>
								<p class="emph">Last insert was <?php echo $this->lastInsert; ?></p>
						</div> <!-- /.status-message -->
					<?php else : ?>
						<div class="alert"></div>
						<div class="status-message">
							<p>The seach engine is not responding</p>
						</div> <!-- /.status-message -->
					<?php endif; ?>
				</div> <!-- /.status -->
			</div> <!-- /.status-message -->
		</div><!-- /.content-inner -->
	</div><!-- /.content -->
	</div><!-- /.inner -->
<div class="widget">
	<div class="inner">
		<div class="title"><div>Solr Logs</div></div>
		<!-- <div class="sub-title"><div class="sub-title-inner">Last Document Insert: <?php //echo $this->lastInsert; ?></div></div> -->
		<!-- <div class="sub-title"><div class="sub-title-inner">Mechanism: <?php //echo $this->repositoryMechanism; ?></div> </div> -->
		<div class="content">
		<div class="content-inner">
		<table id="logs" class="adminlist"> 
		<?php foreach ($this->logs as $log): ?>
		<?php if ($log != ''): ?>
		<tr>
			<td>
				<?php echo $log; ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
		</table>
		<a class="action" href="<?php echo Route::url('index.php?option='.$this->option.'&controller='.$this->controller.'&task=viewLogs'); ?>">View Full Log</a>
		</div><!-- /.content-inner -->
	</div><!-- /.content -->
	</div><!-- /.inner -->
</div><!-- /.widget .code -->


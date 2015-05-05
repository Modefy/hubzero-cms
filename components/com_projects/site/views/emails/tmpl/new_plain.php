<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$base 	    = Request::root();
$sef 		= Route::url('index.php?option=' . $this->option . '&alias=' . $this->project->get('alias'));
$sef_browse = Route::url('index.php?option=' . $this->option . '&task=browse');

$link = rtrim($base, DS) . DS . trim($sef, DS);
$browseLink = rtrim($base, DS) . DS . trim($sef_browse, DS);

$message  = $this->project->owner('name') . ' ' .Lang::txt('COM_PROJECTS_EMAIL_STARTED_NEW_PROJECT');
$message .= ' "' . $this->project->get('title') . '"' . "\n";
$message .= '-------------------------------' . "\n";
$message .= Lang::txt('COM_PROJECTS_PROJECT') . ': ' . $this->project->get('title') . ' (' . $this->project->get('alias') . ')' . "\n";
$message .= ucfirst(Lang::txt('COM_PROJECTS_CREATED')) . ' '
		 . Date::of($this->project->get('created'))->format('M d, Y') . ' '
		 . Lang::txt('COM_PROJECTS_BY') . ' ';
$message .= $this->project->groupOwner()
			 ? $this->project->groupOwner('cn') . ' ' . Lang::txt('COM_PROJECTS_GROUP')
			 : $this->project->owner('name');
$message .= "\n";

if ($this->project->isPublic())
{
	$message .= Lang::txt('COM_PROJECTS_EMAIL_URL') . ': ' . $link . "\n";
}
$message .= '-------------------------------' . "\n\n";
$message .= Lang::txt('COM_PROJECTS_EMAIL_PRIVACY') . ': ';
$message .= !$this->project->isPublic()
			? Lang::txt('COM_PROJECTS_EMAIL_PRIVATE') . "\n"
			: Lang::txt('COM_PROJECTS_EMAIL_PUBLIC') . "\n";

if ($this->project->config()->get('restricted_data', 0))
{
	$message .= Lang::txt('COM_PROJECTS_EMAIL_HIPAA') . ': ' . $this->project->params->get('hipaa_data') . "\n";
	$message .= Lang::txt('COM_PROJECTS_EMAIL_FERPA') . ': ' . $this->project->params->get('ferpa_data') . "\n";
	$message .= Lang::txt('COM_PROJECTS_EMAIL_EXPORT') . ': ' . $this->project->params->get('export_data') . "\n";
	if ($this->project->params->get('followup'))
	{
		$message .= Lang::txt('COM_PROJECTS_EMAIL_FOLLOWUP_NEEDED') . ': ' . $this->project->params->get('followup') . "\n";
	}
	$message .= '-------------------------------' . "\n\n";
}
if ($this->project->config()->get('grantinfo', 0))
{
	$message .= Lang::txt('COM_PROJECTS_EMAIL_GRANT_TITLE') . ': ' . $this->project->params->get('grant_title') . "\n";
	$message .= Lang::txt('COM_PROJECTS_EMAIL_GRANT_PI') . ': ' . $this->project->params->get('grant_PI') . "\n";
	$message .= Lang::txt('COM_PROJECTS_EMAIL_GRANT_AGENCY') . ': ' . $this->project->params->get('grant_agency') . "\n";
	$message .= Lang::txt('COM_PROJECTS_EMAIL_GRANT_BUDGET') . ': ' . $this->project->params->get('grant_budget') . "\n";
}
$message .= '-------------------------------' . "\n\n";

if ($this->project->config()->get('ginfo_group', 0))
{
	$message .= Lang::txt('COM_PROJECTS_EMAIL_LINK_SPS') . "\n";
	$message .= $browseLink . '?reviewer=sponsored' . "\n\n";
}

if ($this->project->config()->get('sdata_group', 0))
{
	$message .= Lang::txt('COM_PROJECTS_EMAIL_LINK_HIPAA') . "\n";
	$message .= $browseLink . '?reviewer=sensitive' . "\n";
}

$message = str_replace('<br />', '', $message);
$message = preg_replace('/\n{3,}/', "\n\n", $message);

echo $message;

?>
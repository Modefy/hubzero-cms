<?php
/**
 * @package		HUBzero CMS
 * @author		Shawn Rice <zooley@purdue.edu>
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

?>
<div id="content-header" class="full">
	<h2><?php echo $this->title; ?></h2>
</div>

<div id="introduction" class="section">
	<div class="aside">
		<h3>Help</h3>
		<ul>
			<li><a href="/kb/citations/faq">Citations FAQ</a></li>
			<li><a href="<?php echo JRoute::_('index.php?option='.$option.a.'task=add'); ?>">Submit a citation</a></li>
		</ul>
	</div><!-- / .aside -->
	<div class="subject">
		<div class="two columns first">
			<h3>What are citations?</h3>
			<p>The following are works that have cited or referenced this site or some piece of site content. Each citation links to the piece of content it references and is downloadable in either BibTex or EndNote format.</p>
		</div>
		<div class="two columns second">
			<h3>Can I submit a citation?</h3>
			<p>Yes! You can submit a citation for a piece of work that has referenced site content by <a href="<?php echo JRoute::_('index.php?option='.$option.a.'task=add'); ?>">clicking here</a>. However, please search or browse the existing citations to ensure no duplicate entries.</p>
		</div>
		<div class="clear"></div>
	</div><!-- / .subject -->
	<div class="clear"></div>
</div><!-- / #introduction.section -->

<div class="section">
	
	<div class="four columns first">
		<h2>Find a citation</h2>
	</div><!-- / .four columns first -->
	<div class="four columns second third fourth">
		<div class="two columns first">
			<form action="<?php echo JRoute::_('index.php?option='.$option.a.'task=browse'); ?>" method="get" class="search">
				<fieldset>
					<p>
						<input type="text" name="search" value="" />
						<input type="submit" value="Search" />
					</p>
				</fieldset>
			</form>
		</div><!-- / .two columns first -->
		<div class="two columns second">
			<div class="browse">
				<p><a href="<?php echo JRoute::_('index.php?option='.$option.a.'task=browse'); ?>">Browse the list of available citations</a></p>
			</div><!-- / .browse -->
		</div><!-- / .two columns second -->
	</div><!-- / .four columns second third fourth -->
	<div class="clear"></div>

	<div class="four columns first">
		<h2>Metrics</h2>
	</div><!-- / .four columns first -->
	<div class="four columns second third fourth">
		<div id="statistics">
<?php
$yearlystats = $this->yearlystats;
$cls = 'even';
$tot = 0;
$rows = array();
foreach ($yearlystats as $year=>$amt) 
{
	$cls = ($cls == 'even') ? 'odd' : 'even';
	
	$tr  = t.t.'<tr class="'.$cls.'">'.n;
	$tr .= t.t.t.'<th class="textual-data">'.$year.'</th>'.n;
	$tr .= t.t.t.'<td class="numerical-data">'.$amt['affiliate'].'</td>'.n;
	$tr .= t.t.t.'<td class="numerical-data">'.$amt['non-affiliate'].'</td>'.n;
	$tr .= t.t.t.'<td class="numerical-data highlight">'.(intval($amt['affiliate']) + intval($amt['non-affiliate'])).'</td>'.n;
	$tr .= t.t.'</tr>'.n;
	
	$rows[] = $tr;
	
	$tot += (intval($amt['affiliate']) + intval($amt['non-affiliate']));
}

$html  = '<table>'.n;
$html .= t.'<caption>'.JText::_('CITATIONS_TABLE_METRICS_YEAR').'</caption>'.n;
$html .= t.'<thead>'.n;
$html .= t.t.'<tr>'.n;
$html .= t.t.t.'<th scope="col" class="textual-data">'.JText::_('CITATIONS_YEAR').'</th>'.n;
$html .= t.t.t.'<th scope="col" class="numerical-data"><sup><a href="#fn-1">1</a></sup> '.JText::_('CITATIONS_AFFILIATED').'</th>'.n;
$html .= t.t.t.'<th scope="col" class="numerical-data"><sup><a href="#fn-1">1</a></sup> '.JText::_('CITATIONS_NONAFFILIATED').'</th>'.n;
$html .= t.t.t.'<th scope="col" class="numerical-data">'.JText::_('CITATIONS_TOTAL').'</th>'.n;
$html .= t.t.'</tr>'.n;
$html .= t.'</thead>'.n;
$html .= t.'<tfoot>'.n;
$html .= t.t.'<tr class="summary">'.n;
$html .= t.t.t.'<th class="numerical-data" colspan="3">'.JText::_('CITATIONS_TOTAL').'</th>'.n;
$html .= t.t.t.'<td class="numerical-data highlight">'.$tot.'</td>'.n;
$html .= t.t.'</tr>'.n;
$html .= t.'</tfoot>'.n;
$html .= t.'<tbody>'.n;
$html .= implode('',$rows);
$html .= t.'</tbody>'.n;
$html .= '</table>'.n;

//---

$typestats = $this->typestats;
$cls = 'even';
$rows = array();
$j = 0;
$data_arr = array();
$data_arr['text'] = null;
$data_arr['hits'] = null;
foreach ($typestats as $type=>$stat) 
{
	$data_arr['text'][$j] = trim($type);
	$data_arr['hits'][$j] = $stat;
	$j++;
}

$polls_graphwidth = 200;
$polls_barheight  = 2;
$polls_maxcolors  = 5;
$polls_barcolor   = 0;
$tabcnt = 0;
$colorx = 0;
$maxval = 0;

array_multisort( $data_arr['hits'], SORT_NUMERIC, SORT_DESC, $data_arr['text'] );

foreach ($data_arr['hits'] as $hits) 
{
	if ($maxval < $hits) {
		$maxval = $hits;
	}
}
$sumval = array_sum( $data_arr['hits'] );

for ($i=0, $n=count($data_arr['text']); $i < $n; $i++) 
{
	$text =& $data_arr['text'][$i];
	$hits =& $data_arr['hits'][$i];
	if ($maxval > 0 && $sumval > 0) {
		$width = ceil( $hits*$polls_graphwidth/$maxval );
		$percent = round( 100*$hits/$sumval, 1 );
	} else {
		$width = 0;
		$percent = 0;
	}
	$tdclass='';
	if ($polls_barcolor==0) {
		if ($colorx < $polls_maxcolors) {
			$colorx = ++$colorx;
		} else {
			$colorx = 1;
		}
		$tdclass = 'color'.$colorx;
	} else {
		$tdclass = 'color'.$polls_barcolor;
	}
	
	$cls = ($cls == 'even') ? 'odd' : 'even';
	
	$tr  = t.t.'<tr class="'.$cls.'">'.n;
	$tr .= t.t.t.'<th class="textual-data">'.$text.'</th>'.n;
	$tr .= t.t.t.'<td class="numerical-data">'.n;
	$tr .= t.t.t.t.'<div class="graph">'.n;
	$tr .= t.t.t.t.t.'<strong class="bar '.$tdclass.'" style="width: '.$percent.'%;"><span>'.$percent.'%</span></strong>'.n;
	$tr .= t.t.t.t.'</div>'.n;
	$tr .= t.t.t.'</td>'.n;
	$tr .= t.t.t.'<td class="numerical-data">'.$hits.'</td>'.n;
	$tr .= t.t.'</tr>'.n;
	
	$rows[] = $tr;
	
	$tabcnt = 1 - $tabcnt;
}

$html .= '<table>'.n;
$html .= t.'<caption>'.JText::_('CITATIONS_TABLE_METRICS_TYPE').'</caption>'.n;
$html .= t.'<thead>'.n;
$html .= t.t.'<tr>'.n;
$html .= t.t.t.'<th scope="col" class="textual-data">'.JText::_('CITATIONS_TYPE').'</th>'.n;
$html .= t.t.t.'<th scope="col" class="textual-data">'.JText::_('CITATIONS_PERCENT').'</th>'.n;
$html .= t.t.t.'<th scope="col" class="numerical-data">'.JText::_('CITATIONS_TOTAL').'</th>'.n;
$html .= t.t.'</tr>'.n;
$html .= t.'</thead>'.n;
$html .= t.'<tfoot>'.n;
$html .= t.t.'<tr class="summary">'.n;
$html .= t.t.t.'<th class="text-data">'.JText::_('CITATIONS_TOTAL').'</th>'.n;
$html .= t.t.t.'<td class="textual-data">100%</td>'.n;
$html .= t.t.t.'<td class="numerical-data">'.$sumval.'</td>'.n;
$html .= t.t.'</tr>'.n;
$html .= t.'</tfoot>'.n;
$html .= t.'<tbody>'.n;
$html .= implode('',$rows);
$html .= t.'</tbody>'.n;
$html .= '</table>'.n;
$html .= '<div class="footnotes"><hr />
	<ol><li><a name="fn-1"></a>Affiliation refers to if the author of the work that cited a piece of this site\'s content was in any way affiliated with the parent organization of the site.</li></ol>
	</div>'.n;

echo $html;

?>
		</div><!-- /#statistics -->
	</div><!-- / .four columns second third fourth -->
	<div class="clear"></div>

</div><!-- / .section -->

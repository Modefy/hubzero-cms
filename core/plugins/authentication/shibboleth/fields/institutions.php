<?php
$schemes = array(
	'http'  => 1,
	'https' => 1
);
// thumbnail service
if (isset($_GET['img']) && isset($schemes[parse_url($_GET['img'], \PHP_URL_SCHEME)]))
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $_GET['img']);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	if (($data = curl_exec($curl)) && ($gd = @imagecreatefromstring($data))) {
		$w = imagesx($gd);
		$h = imagesy($gd);
		if ($w && $h) {
			$max = max($w, $h);
			$ow = floor(28 * $w/$max);
			$oh = floor(28 * $h/$max);
			$out = imagecreatetruecolor($ow, $oh);
			imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
			imagecopyresampled(
				$out, // dest
				$gd, // src
				0, 0, // (dest x, dest y)
				0, 0, // (src x, src y)
				$ow, $oh, // (dest w, dest h)
				$w, $h // (src w, src h)
			);
			header('Content-type: text/plain');
			echo 'data:image/png;base64,';
			ob_start();
			imagepng($out);
			echo base64_encode(ob_get_clean());
		}
	}
	curl_close($curl);
	exit();
}
defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');


class JFormFieldInstitutions extends JFormField
{
	private static $mdSource = 'https://wayf.incommonfederation.org/InCommon/InCommon-metadata.xml';
	private static $mdDest = '/www/tmp/InCommon-metadata-fallback.xml';

	protected function getInput()
	{
		Document::addScript('/core/plugins/authentication/shibboleth/assets/js/admin.js');
		Document::addStyleSheet('/core/plugins/authentication/shibboleth/assets/css/jquery-ui.css');
		Document::addStyleSheet('/core/plugins/authentication/shibboleth/assets/css/admin.css');

		$html = array();
		$a = function($str)
		{
			return str_replace('"', '&quot;', $str);
		};
		$val = json_decode($this->value, TRUE);
		$html[] = '<div class="shibboleth" data-iconify="'.$a(preg_replace('#^'.preg_quote(PATH_CORE).'#', '', __FILE__)).'">';
		$html[] = '<p class="xml-source"><label>Shibboleth ID provider configuration file: <input type="text" name="xmlPath" value="'.$a($val['xmlPath']).'" /></label></p>';
		list($val['xmlRead'], $val['idps']) = self::getIdpList($val);
		$html[] = '<p class="info">Save your changes to retry loading ID providers from this file</p>';
		$html[] = '<input type="hidden" class="serialized" name="'.$this->name.'" value="'.$a(json_encode($val)).'" />';
		$html[] = '</div>';
		// rest of the form is managed on the client side
		return implode("\n", $html);
	}

	private static function getIdpList($val, $alwaysUpdate = TRUE)
	{
		// list is up to date
		if (($mtime = $val['xmlPath'].':'.filemtime($val['xmlPath'])) == $val['xmlRead'] && !$alwaysUpdate)
		{
			return array($mtime, $val['idps']);
		}
		if (!($xml = simplexml_load_file($val['xmlPath'])))
		{
			return array(null, 'Failed to parse XML from this path');
		}
		$xml->registerXPathNamespace('shib', 'urn:mace:shibboleth:2.0:native:sp:config');

		$curl = curl_init();
		$rv = array();
		foreach ($xml->xpath('//shib:SSO') as $item)
		{
			$entityId = (string)$item->attributes()->entityID;
			curl_setopt($curl, CURLOPT_URL, $entityId);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$item = array(
				'entity_id' => $entityId,
				'label'    => NULL,
				'host'     => NULL,
				'logo'     => NULL
			);
			if (!($idp = curl_exec($curl)))
			{
				$item['error'] = 'Failed to fetch metadata';
			}
			else if (!($idp = simplexml_load_string($idp)))
			{
				$item['error'] = 'Failed to parse metadata';
			}
			else
			{
				$idp->registerXPathNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:metadata');
				// look in a few places for the display name
				foreach (array(
					'//mdui:DisplayName',        // most preferred, ui extension offers prefs for this specific use case
					'//saml:OrganizationDisplayName', // good fallback
					'//saml:OrganizationName'         // ok fallback
				) as $xp)
				{
					if (($name = $idp->xpath($xp)))
					{
						$item['label'] = (string)$name[0];
						break;
					}
				}
				if (($orgUrl = $idp->xpath('//saml:OrganizationURL')))
				{
					$item['host'] = preg_replace('/^.*[.]([^.]+[.][^.]+)$/', '$1', parse_url($orgUrl[0], \PHP_URL_HOST));
				}
				$item['logo'] = $idp->xpath('//mdui:Logo');
				$item['logo'] = $item['logo'] ? (string)$item['logo'][0] : NULL;
			}
			$rv[] = $item;
		}
		curl_close($curl);
		$rv = array_merge($rv, self::getResearchAndScholarshipIdps($curl));
		return array($mtime, $rv);
	}

	private static function getResearchAndScholarshipIdps($ch)
	{
		// fetch the latest InCommon metadata, if needed
		curl_setopt($ch, CURLOPT_URL, self::$mdSource);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		if (file_exists(self::$mdDest)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['If-Modified-Since: '.gmdate('D, d M Y H:i:s \G\M\T', filemtime(self::$mdDest))]);
			$xml = curl_exec($ch);
			if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 304) {
				$xml = file_get_contents(self::$mdDest);
			}
			else {
				file_put_contents(self::$mdDest, $xml);
			}
		}
		else {
			$xml = curl_exec($ch);
			file_put_contents(self::$mdDest, curl_exec($ch));
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, []);

		$xp = new \SimpleXMLElement($xml);
		foreach ($xp->getNamespaces(TRUE) as $name=>$url) {
			$xp->registerXPathNamespace($name ? $name : 'base', $url);
		}
		$rv = [];
		// select entities having the saml attribute indicating that they are research & scholarship category members
		// being members ourselves, we can get attributes about users released fromt these entities
		foreach ($xp->xpath('//base:EntityDescriptor[
			base:Extensions/
				mdattr:EntityAttributes/
					saml:Attribute[attribute::Name="http://macedir.org/entity-category-support"]/
						saml:AttributeValue[text()="http://id.incommon.org/category/research-and-scholarship" or text()="http://refeds.org/category/research-and-scholarship"]
			]') as $entity) {
			// easier to work with as an array, the SimpleXMLElement class is bizarre
			$entity = (array)$entity;
			$id = $entity['@attributes']['entityID'];
			$title = (string)$xp->xpath('//base:EntityDescriptor[attribute::entityID="'.$id.'"]//mdui:DisplayName')[0];
			preg_match('/([^.:]+[.][^.]+?)(?:[\/]|$)/', $id, $ma);
			$host = $ma[1];
			$logo = $xp->xpath('//base:EntityDescriptor[attribute::entityID="'.$id.'"]//mdui:Logo');
			$logo = isset($logo[0]) ? (string)$logo[0] : 'https://'.$host.'/favicon.ico';
//			curl_setopt($ch, CURLOPT_URL, isset($logo[0]) ? (string)$logo[0] : 'https://'.$host.'/favicon.ico');
//			$iconData = curl_exec($ch);
//			$icon = NULL;
//			if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
//				$ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
//				if ($ctype == 'text/plain') {
//					$ctype = 'image/x-icon';
//				}
//				$icon = 'data:'.$ctype.';base64,'.base64_encode($iconData);
//			}
			$rv[] = [
				'entity_id' => $id,
				'label'     => $title,
				'host'      => $host,
				'logo'      => $logo
			];
		}
		return $rv;
	}
}

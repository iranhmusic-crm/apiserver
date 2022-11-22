<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\classes\base;

class BaseExtension extends \yii\base\Component
{
	public $extensionModel = null;
	// public $pluginParametersFieldName = null;

	// public function __get($name)
	// {
	// 	$getter = 'get' . ucfirst($name);
	// 	if (method_exists($this, $getter))
	// 	{
	// 		// read property, e.g. getName()
	// 		return $this->$getter();
	// 	}

	// 	if (empty($this->pluginParametersFieldName) == false && $this->extensionModel !== null)
	// 	{
	// 		$val = null;
	// 		if (isset($this->extensionModel->{$this->pluginParametersFieldName}[$name]))
	// 			$val = $this->extensionModel->{$this->pluginParametersFieldName}[$name];

	// 		if ($val === null)
	// 		{
	// 			$params = $this->getParameters();
	// 			foreach ($params as $param)
	// 			{
	// 				if ($param['id'] == $name)
	// 				{
	// 					if (isset($param['default']))
	// 						return $param['default'];
	// 					break;
	// 				}
	// 			}
	// 		}

	// 		return $val;
	// 	}

	// 	return null; //parent::__get($name);
	// }

	public function getTitle()
	{
		return __CLASS__;
		// throw new \Exception('not implemented.');
	}

	public function isEnable()
	{
		return true;
	}

	public function getParameters()
	{
		return [];
	}

	/*
	public function formatParameters($items)
	{
		if ($items === null)
			return '';

		$mapLabels = GeoDivisionMapModel::getMapArray();
		$table = Html::table(['class' => 'table table-bordered table-striped']);
		$params = $this->getParameters();
		foreach ($params as $pv)
		{
			if (!isset($items[$pv['id']]))
				continue;

			$v = $items[$pv['id']];

			$row = $table->row();
			$row->headCell($pv['label']);

			$valueCellOptions = [];
			if (isset($pv['style']))
				$valueCellOptions['style'] = $pv['style'];

			switch ($pv['type'])
			{
				case 'app/gdvmap':
					if (isset($mapLabels[$v]))
						$row->cell($mapLabels[$v], $valueCellOptions);
					else
						$row->cell($v, $valueCellOptions);
					break;

				case 'combo':
				case 'dropdown':
					if (isset($pv['data']) && isset($pv['data'][$v]))
						$row->cell($pv['data'][$v], $valueCellOptions);
					break;

				case 'multi-select':
					$s = [];
					foreach ($v as $kk => $vv)
						$s[] = $pv['data'][$kk];
					$row->cell(implode("<br>", $s), $valueCellOptions);
					break;

				case 'bool':
					if (is_string($v))
						$v = intval($v);
					$row->cell(Yii::$app->formatter->asKZIcon($v, ['plugin' => 'glyph']), $valueCellOptions);
					break;

				case 'password':
					$row->cell('*****', $valueCellOptions);
					break;

				// case 'multi-string':
				// case 'multi-text':
				default:
					Html::addCssStyle($valueCellOptions, [
						'white-space' => 'normal',
						'overflow-wrap' => 'break-word',
						'word-wrap' => 'break-word',
						'word-break' => 'break-all',
					]);
					$row->cell($v, $valueCellOptions);
					break;
			}
		}

		return $table->toString();
	}
*/

	//--helpers:
	function post2https($url, $params=null, $options=null)
	{
		$fields_string = '';
		if ($params && count($params))
		{
			foreach ($params as $key=>$value)
			{
				$fields_string .= $key . '=' . $value . '&';
			}
			$fields_string = substr($fields_string, 0, -1);
// die('>>' . var_dump($fields_string) . '<<');
		}

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($params && count($params))
		{
			curl_setopt($ch, CURLOPT_POST, count($params));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		if ($options && count($options))
		{
			foreach ($options as $k => $v)
			{
				curl_setopt($ch, $k, $v);
			}
		}

		// execute post
		$res = curl_exec($ch);
// die('>>' . var_dump($res) . '<<');

		//close connection
		curl_close($ch);
		return $res;
	}

	function makeXMLTree($data)
	{
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $values, $tags);
		xml_parser_free($parser);

		$ret = [];
		$hash_stack = [];
		// $ret = json_encode(ArrayHelper::map($values, "tag", "value"));
		// $ret = json_decode($ret, false);
// die(print_r($values, true));
		foreach ($values as $key => $val)
		{
			switch ($val['type'])
			{
				case 'open':
					array_push($hash_stack, $val['tag']);
					break;
				case 'close':
					array_pop($hash_stack);
					break;
				case 'complete':
					array_push($hash_stack, $val['tag']);
					if (isset($val['value']))
					{
						$val = $val['value'];
						if (($val === False) || ($val === FALSE) || ($val === 'False') || ($val === 'FALSE'))
							$val = false;
						elseif (($val === True) || ($val === TRUE) || ($val === 'True') || ($val === 'TRUE'))
							$val = true;
						eval("\$ret['" . implode("']['", $hash_stack) . "'] = '{$val}';");
					}
					array_pop($hash_stack);
					break;
			}
		}
// die(print_r($ret, true));
		return $ret;
	}

}

<?
class keywordExtractor {
	var $keywordGroup = array();
	var $keywordStems = array();
	var $resourceID = -1;
	function __construct($rawText = '') {
		if($rawText != '') {
			$this->extractFromText($rawText);
		}
	}
	function addNewKeyword($keyword = '') {
		$keyword = trim($keyword);
		if($keyword != '') {
			$existingKey = -1;
			foreach($this->keywordGroup as $key=>$existingKeyword) {
				if($keyword == $existingKeyword->keyword) {
					$existingKey = $key;
					break;
				}
			}
			if($existingKey != -1) {
				$this->keywordGroup[$existingKey]->addInstance($this->resourceID);
			}
			else {
				$this->keywordGroup[] = new keyword($keyword,$this->resourceID);
			}
			$words = explode(" ",$keyword);
			if(count($words) > 1) {
				if(!array_key_exists($words[0],$this->keywordStems)) {
					$this->keywordStems[$words[0]][] = $keyword;
				}
				else {
					if(!in_array($keyword,$this->keywordStems[$words[0]])) {
						$this->keywordStems[$words[0]][] = $keyword;
					}
				}
			}
		}
	}
	function extractFromText($rawText = '') {
		if($rawText != '') {
			$pieces = array();
			$strings = array();
			$words = array();
			$stopWords = getBigData('stop_words');
			$cleanText = strtolower($rawText);

			$cleanText = preg_replace('/<script\b[^>]*>([\s\S]*?)<\/script>/','',$cleanText);
			$cleanText = preg_replace('/<style\b[^>]*>([\s\S]*?)<\/style>/','',$cleanText);

			$pieces = preg_split('/(<\/?(p|br|h1|h2|h3|h4|h5|h6|h7|td|a|li)( \S*)?>)/',$cleanText); //split keywords by tags
			foreach($pieces as $thisPiece) {
				$thisPiece = strip_tags($thisPiece);
				$thisPiece = html_entity_decode($thisPiece);
				$thisPiece = preg_replace('/\s+/',' ',$thisPiece);
				$thisPiece = preg_replace('/[[:^print:]]/','',$thisPiece);
				$unbrokenStrings = preg_split('/[:;,.-<>+=\/\(\)\{\}\[\]|&?!"]/',$thisPiece);
				foreach($unbrokenStrings as $thisString) {
					$words = explode(" ",$thisString);
					$thisPhrase = array();
					foreach($words as $thisWord) {
						if(in_array($thisWord,$stopWords)) {
							$this->addNewKeyword(implode(" ",$thisPhrase));
							$thisPhrase = array();
						}
						else {
							$this->addNewKeyword($thisWord);
							$thisPhrase[] = $thisWord;
						}
					}
					$this->addNewKeyword(implode(" ",$thisPhrase));
				}
			}
		}
	}
}
class keyword {
	var $keywordID = -1;
	var $keyword = '';
	var $sentimentScore = '';
	var $resources = array();
	var $totalInstances = 0;

	function __construct($keyword = '',$resourceID = -1) {
		if($keyword != '') {
			$this->keyword = $keyword;
			$this->resources[] = array('resource_id' => $resourceID,'density' => 1);
			$this->totalInstances = 1;
		}
	}
	function addInstance($resourceID = -1) {
		foreach($this->resources as $key=>$thisResource) {
			if($thisResource['resource_id'] == $resourceID) {
				$this->resources[$key]['density']++;
				$this->totalInstances++;
			}
		}
	}
	function toJSON() {
		$data = array(
			'se' => $this->sentimentScore,
			're' => $this->resources,
			'ti' => $this->totalInstances
		);
		return json_encode($data);
	}
	function fromJSON($json) {
		$data = json_decode($json,true);
		$this->sentimentScore = $data['se'];
		$this->resources = $data['re'];
		$this->totalInstances = $data['ti'];
	}
}
?>

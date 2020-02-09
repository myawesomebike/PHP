<html>
<head>
<title>Visual Sentiment Analysis</title>
<style>
.phrase:hover .word.neg1 {
	border:solid;
	border-width:0px;
	border-bottom-width:2px;
	border-color:#FF0000;
}
.phrase:hover .word.neg2 {
	border:double;
	border-width:0px;
	border-bottom-width:3px;
	border-color:#FF0000;
}
.phrase:hover .word.pos1 {
	border:solid;
	border-width:0px;
	border-bottom-width:2px;
	border-color:#00FF00;
}
.phrase:hover .word.pos2 {
	border:double;
	border-width:0px;
	border-bottom-width:3px;
	border-color:#00FF00;
}
.phrase:hover {
	color:#000099;
}
.word.neg1 {
	border:solid;
	border-width:0px;
	border-bottom-width:2px;
	border-color:#FF0000;
}
.word.neg2 {
	border:double;
	border-width:0px;
	border-bottom-width:3px;
	border-color:#FF0000;
}
.word.pos1 {
	border:solid;
	border-width:0px;
	border-bottom-width:2px;
	border-color:#00FF00;
}
.word.pos2 {
	border:double;
	border-width:0px;
	border-bottom-width:3px;
	border-color:#00FF00;
}
.phrase.score-1, .phrase.score-2, .phrase.score-3, .phrase.score-4, .phrase.score-5 {
	border:solid;
	border-width:0px;
	border-color:#FF0000;
}
.phrase.score1, .phrase.score2, .phrase.score3, .phrase.score4, .phrase.score5 {
	border:solid;
	border-width:0px;
	border-color:#00FF00;
}
.phrase.score-1, .phrase.score1 {
	border-bottom-width:1px;
}
.phrase.score-1,.phrase.score2 {
	border-bottom-width:2px;
}
.phrase.score-3,.phrase.score3 {
	border-bottom-width:3px;
}
.phrase.score-4,.phrase.score4 {
	border-bottom-width:4px;
}
.phrase.score-5,.phrase.score5 {
	border-bottom-width:5px;
}
.phrase:hover {
	border-color:rgba(255,0,0,0.25);
}
.phrase.score-1:hover, .phrase.score-2:hover, .phrase.score-3:hover, .phrase.score-4:hover, .phrase.score-5:hover {
	border-color:rgba(255,0,0,0.25);
}
.phrase.score1:hover, .phrase.score2:hover, .phrase.score3:hover, .phrase.score4:hover, .phrase.score5:hover {
	border-color:rgba(0,255,0,0.25);
}
.phrase {
	position:relative;
	border-width:0px;
}
.phrase .flag {
	display:none;
}
.phrase:hover .flag {
	display:inline-block;
	position:absolute;
	left:10px;
	top:100%;
	background-color:#FFFFFF;
	font-size:9px;
	z-index:10000;
	margin-top:6px;
	padding:2px;
	border:solid;
	border-width:0px;
	border-left-width:4px;
	border-color:#0000FF;
	box-shadow:1px 1px 2px #666666;
}
.stopWord {
	color:#666666;
}
</style>
</head>
<body>
<form action="sentiment-analysis.php">
<textarea name="input" cols=100 rows=5></textarea><br>
<input type="submit" value="Analyze Sentiment">
</form>
<?
$sentiment = array('**omited for size**')
$stopWords = array('**omited for size**')

$newSentiment = array();

foreach($sentiment as $thisSentiment) {
	$newSentiment[$thisSentiment['word']] = array('type' => $thisSentiment['type'], 'pos' => $thisSentiment['pos'], 'stemmed' => $thisSentiment['stemmed'], 'polarity' => trim($thisSentiment['polarity']));
}

$inputText = @$_GET['input'];

$sentimentScore = array();
$sp = chr(29);

$inputText = $sp.$inputText;
$inputText = str_ireplace(".",$sp.".".$sp,$inputText);
$inputText = str_ireplace(",",$sp.",".$sp,$inputText);
$inputText = str_ireplace('"',$sp.'"'.$sp,$inputText);
$inputText = str_ireplace(" ",$sp." ",$inputText);
$words = explode($sp,$inputText);

$trimWords = array();
$phraseWords = array();
foreach($words as $thisWord) {
	$phraseWords[] = array($thisWord,null,0);
	$trimWords[] = trim($thisWord);
}
$words = $trimWords;
$phrases = array();

$phraseStart = 0;
$phraseLength = 1;

$overallScore = 0;
foreach($words as $key=>$thisWord) {
	$thisWordScore = scoreWord($thisWord);
	$overallScore += $thisWordScore;
	if(in_array($thisWord,$stopWords)) {
		$thisPhrase = array_slice($words,$phraseStart,$phraseLength -1);
		$thisPhrase = implode(" ",$thisPhrase);
		if($thisPhrase != '') {
			$phrases[] = trim($thisPhrase);
		}

		for($i = 0; $i < $phraseLength-1; $i++) {
			$phraseWords[$phraseStart + $i][1] = count($phrases) - 1;
		}
		$phraseLength = 0;
		$phraseStart = $key + 1;
	}
	$phraseWords[$key][2] = $thisWordScore;
	$phraseLength++;
}

echo 'Overall score:'.$overallScore.'<br>';

$scoredPhrases = array();

foreach($phrases as $thisPhrase) {
	$phraseScore = 0;
	$words = explode(" ",$thisPhrase);
	foreach($words as $thisWord) {
		$phraseScore += scoreWord($thisWord);
	}
	$scoredPhrases[] = array('phrase' => $thisPhrase,'score' => $phraseScore);
}

$thisPhrase = -1;
echo '<span>';
foreach($phraseWords as $thisWord) {
	if($thisWord[1] != $thisPhrase) {
		if(array_key_exists($thisPhrase,$scoredPhrases)) {
			echo '<div class="flag">Score:'.$scoredPhrases[$thisPhrase]['score'].'</div>';
		}
		$thisPhrase = $thisWord[1];
		echo '</span><span id="'.$thisPhrase.'" class="phrase';
		if(array_key_exists($thisPhrase,$scoredPhrases)) {
			$thisPhraseScore = $scoredPhrases[$thisPhrase]['score'];
			if($thisPhraseScore < 5 || $thisPhraseScore > -5) {
				echo ' score'.$thisPhraseScore;
			}
			elseif ($thisPhraseScore > 5) {
				echo ' score5';
			}
			elseif ($thisPhraseScore < -5) {
				echo ' score-5';
			}
		}
		echo '">';
	}
	echo '<span class="word';
	if($thisWord[2] == 1) { echo ' pos1'; }
	if($thisWord[2] == 2) { echo ' pos2'; }
	if($thisWord[2] == -1) { echo ' neg1'; }
	if($thisWord[2] == -2) { echo ' neg2'; }
	if($thisWord[1] == null) { echo ' stopWord'; }
	echo '">'.$thisWord[0].'</span>';
}

function scoreWord($word) {
	global $newSentiment;
	$score = 0;
	if(array_key_exists($word,$newSentiment)) {
		if($newSentiment[$word]['polarity'] == "positive") {
			$score = 1;
		}
		elseif($newSentiment[$word]['polarity'] == "negative") {
			$score = -1;
		}
		if($newSentiment[$word]['type'] == 'strongsubj') {
			$score = $score * 2;
		}
	}
	return $score;
}
?>
</body>
</html>

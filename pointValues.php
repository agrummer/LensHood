<?php
// Point values for the entire site

// UPLOADING
$uploadPoints = 1;	// points for uploading a new work

// COMMENTS
$commentPoints['high'] = 5; 	// points to be awarded for making a comment that gets a high feedback rating
$commentPoints['medium'] = 2;
$commentPoints['low'] = 1;
$commentPoints['rejected'] = 0;	// no points awarded for a comment that gets rejected
$commentPoints['received'] = 2;	// points for the artist of the work being commented on

// CONTESTS
$contestPoints['first'] = 10;	// points to be awarded for first place winner
$contestPoints['second'] = 5;	// points to be awarded for second place winner
$contestPoints['third'] = 3;	// points to be awarded for third place winner
$contestPoints['judgeMin'] = 100;	// min points needed to be a contest judge
$contestPoints['judgePay'] = 2;	// points to pay judges for their help judging
$contestPoints['creator'] = 4;	// points to the creator of the contest for their idea if it was a success
$contestJudgePeriod = 5;		// number of days to allow for judges to judge contests

// QUESTIONS
$answerPoints['accepted'] = 10; 	// points to be awarded for answering a question and having that answer accepted
$answerPoints['maybe'] = 4;		// points to be awarded for answering a question but having the asker request more responses
$answerPoints['rejected'] = 0;	// no points awarded if asker rejects answer

// TUTORIALS
$tutorialPoints['make'] = 5;	// points to be awarded for making a tutorial
$tutorialPoints['high'] = 5;		// points to be awarded for receiving high feedback on tutorial
$tutorialPoints['medium'] = 2;
$tutorialPoints['low'] = 1;

// MAIN STREET FEATURE
$featureCost = array(0,5,8,14,17);	// points taken away for purchasing a feature of x days on the main page.
?>
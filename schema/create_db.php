<?php

$link = sqlite_open('master.db', 0666, $sqliteerror);
if (!$link) {
    die('access error'.$sqliteerror);
}

print('access! <br />');

$sql = "
	CREATE TABLE [music] (
		[id] INTEGER NOT NULL,
		[type] INTEGER NOT NULL,
		[mp3] TEXT NOT NULL,
		[title] TEXT NOT NULL,
		[url] TEXT,
		PRIMARY KEY(id,type)
	);
";

/***************

column:id
set hour [0-23]

column:type
set weather
1 -> fine
2 -> rain
3 -> cloud
4 -> snow

column:mp3
set full url

column:url
set full url

***************/

$result_flag = sqlite_query($link, $sql, SQLITE_BOTH, $sqliteerror);

if (!$result_flag) {
    die('error <br />'.$sqliteerror);
}

sqlite_close($link);

print('close <br />');

?>
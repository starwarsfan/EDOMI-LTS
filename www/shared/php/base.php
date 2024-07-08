<?
/*
*/
?><? ?><? function sql_connect()
{
    global $sqldb;
    $sqldb = mysqli_connect(global_sqlHost, global_sqlUser, global_sqlPass);
    if ($sqldb === false) {
        return false;
    } else {
        return true;
    }
}

function sql_disconnect()
{
    global $sqldb;
    $r = mysqli_close($sqldb);
    if (($r === false) || ($r == NULL)) {
        return false;
    } else {
        return $r;
    }
}

function sql_call($n)
{
    global $sqldb;
    $r = mysqli_query($sqldb, $n);
    if (($r === false) || ($r == NULL)) {
        return false;
    } else {
        return $r;
    }
}

function sql_result($n)
{
    if ($n === false) {
        return false;
    } else {
        $r = mysqli_fetch_assoc($n);
        if (is_null($r)) {
            return false;
        } else {
            return $r;
        }
    }
}

function sql_insertId()
{
    global $sqldb;
    $r = mysqli_insert_id($sqldb);
    if (!($r > 0)) {
        return false;
    } else {
        return $r;
    }
}

function sql_affectedRows()
{
    global $sqldb;
    return mysqli_affected_rows($sqldb);
}

function sql_rowCount($n)
{
    $r = mysqli_num_rows($n);
    if (!($r >= 0)) {
        return false;
    } else {
        return $r;
    }
}

function sql_close($n)
{
    if ($n !== false) {
        mysqli_free_result($n);
    }
}

function escapeString($n, $mode = 0)
{
    $n = str_replace(chr(92), '\\' . chr(92), $n);
    $n = str_replace(chr(34), '\"', $n);
    $n = str_replace(chr(39), "\'", $n);
    $n = str_replace(chr(9), '\t', $n);
    if ($mode & 1) {
        $n = str_replace('<', '&lt;', $n);
        $n = str_replace('>', '&gt;', $n);
    }
    if ($mode & 2) {
        $n = htmlspecialchars($n);
    }
    if ($mode & 4) {
        $n = str_replace(chr(10), '<br>', $n);
        $n = str_replace(chr(13), '<br>', $n);
    } else {
        $n = str_replace(chr(10), '\n', $n);
        $n = str_replace(chr(13), '\r', $n);
    }
    return $n;
}

function sql_encodeString($n, $mode = false)
{
    return sql_encodeValue($n, $mode);
}

function sql_nearestNumber($n, $csv)
{
    return getNearestNumericValue($n, $csv);
}

function sql_save($db, $condition, $arr)
{
    if (count($arr) == 0) {
        return false;
    }
    if (isEmpty($condition)) {
        $keys = '';
        $values = '';
        foreach ($arr as $k => $v) {
            $keys .= $k . ',';
            $values .= $v . ',';
        }
        sql_call("INSERT INTO " . $db . " (" . rtrim($keys, ',') . ") VALUES (" . rtrim($values, ',') . ")");
        return sql_insertId();
    } else {
        $keysValues = '';
        foreach ($arr as $k => $v) {
            $keysValues .= $k . '=' . $v . ',';
        }
        if (is_numeric($condition)) {
            $n = 'id=' . $condition;
        } else {
            $n = $condition;
        }
        $r = sql_call("UPDATE " . $db . " SET " . rtrim($keysValues, ',') . " WHERE " . $n);
        if ($r !== false) {
            if (is_numeric($condition)) {
                return $condition;
            } else {
                return true;
            }
        }
    }
    return false;
}

function sql_getAllTables($RAMtables, $like = '%')
{
    $r = '';
    $ss1 = sql_call("SHOW DATABASES LIKE 'edomi" . $like . "'");
    while ($db = sql_result($ss1)) {
        $nameDb = $db[key($db)];
        $ss2 = sql_call("SHOW TABLE STATUS IN " . $nameDb);
        while ($table = sql_result($ss2)) {
            if ($RAMtables || (strtoupper($table['Engine']) == 'MYISAM' && substr($table['Name'], 0, 3) != 'RAM')) {
                $r .= $nameDb . '.' . $table['Name'] . ',';
            }
        }
        sql_close($ss2);
    }
    sql_close($ss1);
    return rtrim($r, ',');
}

function sql_columnExists($db, $n)
{
    $tmp = sql_call("SELECT " . $n . " FROM " . $db . " LIMIT 0,1");
    if (sql_result($tmp)) {
        sql_close($tmp);
        return true;
    } else {
        sql_close($tmp);
        return false;
    }
}

function sql_tableExists($n)
{
    if (sql_call("DESCRIBE " . $n)) {
        return true;
    } else {
        return false;
    }
}

function sql_dbExists($n)
{
    $tmp = sql_call("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='" . $n . "'");
    if (sql_result($tmp)) {
        sql_close($tmp);
        return true;
    } else {
        sql_close($tmp);
        return false;
    }
}

function sql_getCount($db, $n)
{
    $ss1 = sql_call("SELECT COUNT(*) AS anz1 FROM " . $db . " WHERE " . $n);
    if ($nn = sql_result($ss1)) {
        sql_close($ss1);
        return intval($nn['anz1']);
    }
    sql_close($ss1);
    return 0;
}

function sql_getValue($db, $column, $n)
{
    $ss1 = sql_call("SELECT " . $column . " AS anz1 FROM " . $db . " WHERE " . $n);
    if ($nn = sql_result($ss1)) {
        sql_close($ss1);
        return $nn['anz1'];
    }
    sql_close($ss1);
    return null;
}

function sql_getValues($db, $columns, $n)
{
    $ss1 = sql_call("SELECT " . $columns . " FROM " . $db . " WHERE " . $n);
    if ($nn = sql_result($ss1)) {
        sql_close($ss1);
        return $nn;
    }
    sql_close($ss1);
    return false;
}

function sql_getDateTime($n)
{
    if ($n && strtotime($n)) {
        return date('d.m.Y / H:i:s', strtotime($n));
    } else {
        return '';
    }
}

function sql_getDate($n)
{
    if ($n && strtotime($n)) {
        return date('d.m.Y', strtotime($n));
    } else {
        return '';
    }
}

function sql_getTime($n)
{
    if ($n && strtotime($n)) {
        return date('H:i:s', strtotime($n));
    } else {
        return '';
    }
}

function sql_getNow()
{
    return "'" . date('Y-m-d H:i:s') . "'";
}

function sql_encodeValue($n, $allowNull = false, $allowSeparators = false)
{
    $n = utf8_decode($n);
    if ($allowSeparators) {
        $n = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1B]/', '', $n);
    } else {
        $n = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', '', $n);
    }
    $n = addslashes($n);
    $n = str_replace(chr(9), '\t', $n);
    $n = str_replace(chr(10), '\n', $n);
    $n = str_replace(chr(13), '\r', $n);
    $n = utf8_encode($n);
    if (!$allowNull) {
        return $n;
    } else if (!isEmpty($n)) {
        return chr(39) . $n . chr(39);
    } else {
        return 'null';
    }
}

function getNearestNumericValue($value, $csv)
{
    $r = $value;
    if (is_numeric($r)) {
        $max = null;
        $matchid = false;
        $csvarr = array();
        $tmp = explode(',', $csv);
        for ($t = 0; $t < count($tmp); $t++) {
            if (is_numeric(trim($tmp[$t]))) {
                array_push($csvarr, trim($tmp[$t]));
            }
        }
        for ($t = 0; $t < count($csvarr); $t++) {
            if (is_null($max) || abs($value - $csvarr[$t]) < $max) {
                $max = abs($value - $csvarr[$t]);
                $matchid = $t;
                $r = $csvarr[$t];
            }
        }
        return $r;
    } else {
        $tmp = explode(',', $csv);
        return $tmp[0];
    }
}

function sql_decodeValue($n)
{
    $n = str_replace(chr(92), '\\' . chr(92), $n);
    $n = str_replace(chr(34), '\"', $n);
    $n = str_replace(chr(39), "\'", $n);
    $n = str_replace(chr(9), '\t', $n);
    $n = str_replace(chr(10), '\n', $n);
    $n = str_replace(chr(13), '\r', $n);
    return $n;
}

function ajaxEncode($n)
{
    return htmlspecialchars(sql_decodeValue($n));
}

function ajaxEcho($n)
{
    echo htmlspecialchars(sql_decodeValue($n));
}

function ajaxValue($n, $echo = true)
{
    if ($echo) {
        echo sql_decodeValue($n);
    } else {
        return sql_decodeValue($n);
    }
}

function ajaxValueHTML($n)
{
    $n = str_replace(chr(92), '\\' . chr(92), $n);
    $n = str_replace(chr(34), '\"', $n);
    $n = str_replace(chr(39), "\'", $n);
    $n = str_replace(chr(9), '\t', $n);
    $n = str_replace(chr(10), '<br>', $n);
    $n = str_replace(chr(13), '<br>', $n);
    return $n;
}

function getTimestamp()
{
    $t = microtime(true);
    $r[0] = date("Y-m-d H:i:s", $t);
    $r[1] = strval(sprintf("%06d", ($t - floor($t)) * 1000000));
    return $r;
}

function getTimestampRound($mode)
{
    $ts = getTimestamp();
    if ($mode == 1) {
        $ts[0] = date('Y-m-d', strtotime($ts[0] . '-1 day')) . ' 00:00:00';
        $ts[1] = '000000';
    } else if ($mode == 2) {
        $ts[0] = date('Y-m-d', strtotime($ts[0] . '-1 day')) . ' 12:00:00';
        $ts[1] = '500000';
    } else if ($mode == 3) {
        $ts[0] = date('Y-m-d', strtotime($ts[0] . '-1 day')) . ' 23:59:59';
        $ts[1] = '999999';
    } else if ($mode == 11) {
        $ts[0] = date('Y-m-d', strtotime($ts[0])) . ' 00:00:00';
        $ts[1] = '000000';
    } else if ($mode == 12) {
        $ts[0] = date('Y-m-d', strtotime($ts[0])) . ' 12:00:00';
        $ts[1] = '500000';
    } else if ($mode == 13) {
        $ts[0] = date('Y-m-d', strtotime($ts[0])) . ' 23:59:59';
        $ts[1] = '999999';
    } else if ($mode == 21) {
        $ts[0] = date('Y-m-d', strtotime($ts[0] . '1 day')) . ' 00:00:00';
        $ts[1] = '000000';
    } else if ($mode == 22) {
        $ts[0] = date('Y-m-d', strtotime($ts[0] . '1 day')) . ' 12:00:00';
        $ts[1] = '500000';
    } else if ($mode == 23) {
        $ts[0] = date('Y-m-d', strtotime($ts[0] . '1 day')) . ' 23:59:59';
        $ts[1] = '999999';
    }
    return $ts;
}

function getTimestampId()
{
    $t = microtime(true);
    return date("YmdHis", $t) . strval(sprintf("%06d", ($t - floor($t)) * 1000000));
}

function getTimestampVisu()
{
    $t = microtime(true);
    return floor($t) . strval(sprintf("%06d", ($t - floor($t)) * 1000000));
}

function getDateFromTimestampVisu($n)
{
    $tmp = date('d.m.Y/H:i:s', (substr($n, 0, strlen($n) - 6)));
    $r = explode('/', $tmp);
    $r[2] = substr($n, strlen($n) - 6, 6);
    return $r;
}

function getTimestampIdWdayTime()
{
    $t = microtime(true);
    return date("NHis", $t) . strval(sprintf("%06d", ($t - floor($t)) * 1000000));
}

function getMicrotime()
{
    return microtime(true);
}

function getMicrotimeInt()
{
    return intVal(microtime(true) * 1000000);
}

function checkClientServerDateTime($clientDT)
{
    if (global_dateTimeWarning == 0) {
        return 0;
    } else {
        $n = strtotime($clientDT);
        if ($n && !isEmpty($clientDT)) {
            $t = abs($n - strtotime(date('d.m.Y H:i:s')));
            if ($t < (global_dateTimeWarning * 60)) {
                return 0;
            }
        }
        return 1;
    }
}

function checkZSU_date($date, $filter, $auxKoValue)
{
    if ($auxKoValue !== false && $filter[15] >= 1) {
        if ($filter[15] == 1 && $auxKoValue != 1) {
            return false;
        }
        if ($filter[15] == 2 && $auxKoValue == 1) {
            return false;
        }
    }
    $d0 = date('Ymd', strtotime($date));
    $wday0 = intval(date('N', strtotime($date)));
    if ($filter[$wday0] == 0) {
        return false;
    }
    $day0 = intval(date('d', strtotime($date)));
    $month0 = intval(date('m', strtotime($date)));
    $year0 = intval(date('Y', strtotime($date)));
    if ($filter[0] == 0) {
        $dayMatch1 = false;
        $monthMatch1 = false;
        $yearMatch1 = false;
        $dayMatch2 = false;
        $monthMatch2 = false;
        $yearMatch2 = false;
        if (!isEmpty($filter[8]) && !isEmpty($filter[11]) && $filter[11] < $filter[8]) {
            if ($day0 < $filter[8]) {
                $day0 += 31;
            }
            $filter[11] += 31;
        }
        if (!isEmpty($filter[9]) && !isEmpty($filter[12]) && $filter[12] < $filter[9]) {
            if ($month0 < $filter[9]) {
                $month0 += 12;
            }
            $filter[12] += 12;
        }
        if (isEmpty($filter[8]) || $day0 >= $filter[8]) {
            $dayMatch1 = true;
        }
        if (isEmpty($filter[9]) || $month0 >= $filter[9]) {
            $monthMatch1 = true;
        }
        if (isEmpty($filter[10]) || $year0 >= $filter[10]) {
            $yearMatch1 = true;
        }
        if (isEmpty($filter[11]) || $day0 <= $filter[11]) {
            $dayMatch2 = true;
        }
        if (isEmpty($filter[12]) || $month0 <= $filter[12]) {
            $monthMatch2 = true;
        }
        if (isEmpty($filter[13]) || $year0 <= $filter[13]) {
            $yearMatch2 = true;
        }
        if ($dayMatch1 && $dayMatch2 && $monthMatch1 && $monthMatch2 && $yearMatch1 && $yearMatch2) {
            return true;
        }
    } else {
        $dStart = false;
        if (isEmpty($filter[8])) {
            $filter[8] = 1;
        }
        if (isEmpty($filter[9])) {
            $filter[9] = 1;
        }
        if (!isEmpty($filter[10])) {
            $dStart = date('Ymd', strtotime($filter[8] . '.' . $filter[9] . '.' . $filter[10]));
        }
        if (!isEmpty($filter[10]) && isEmpty($filter[13])) {
            $filter[10] = '';
        }
        $dEnd = false;
        if (isEmpty($filter[11])) {
            $filter[11] = 31;
        }
        if (isEmpty($filter[12])) {
            $filter[12] = 12;
        }
        if (!isEmpty($filter[13])) {
            $dEnd = date('Ymd', strtotime($filter[11] . '.' . $filter[12] . '.' . $filter[13]));
        }
        if (isEmpty($filter[10]) && !isEmpty($filter[13])) {
            $filter[13] = '';
        }
        if (isEmpty($filter[10])) {
            $filter[10] = $year0;
        }
        if (isEmpty($filter[13])) {
            $filter[13] = $year0;
        }
        $d1 = date('Ymd', strtotime($filter[8] . '.' . $filter[9] . '.' . $filter[10]));
        $d2 = date('Ymd', strtotime($filter[11] . '.' . $filter[12] . '.' . $filter[13]));
        $tmp1 = sprintf('%04d', $filter[10]) . sprintf('%02d', $filter[9]) . sprintf('%02d', $filter[8]);
        if ($tmp1 != $d1) {
            $d1 = date('Ymd', strtotime('01.' . ($filter[9] + 1) . '.' . $filter[10]));
        }
        $tmp2 = sprintf('%04d', $filter[13]) . sprintf('%02d', $filter[12]) . sprintf('%02d', $filter[11]);
        if ($tmp2 != $d2) {
            $d2 = date('Ymd', strtotime('01.' . ($filter[12] + 1) . '.' . $filter[13] . ' -1 day'));
        }
        if (($dStart === false || intval($d0) >= intval($dStart)) && ($dEnd === false || intval($d0) <= intval($dEnd))) {
            if ($d1 > $d2) {
                if (intval($d0) >= intval($d1) || intval($d0) <= intval($d2)) {
                    return true;
                }
            } else {
                if (intval($d0) >= intval($d1) && intval($d0) <= intval($d2)) {
                    return true;
                }
            }
        }
    }
    return false;
}

function previewTSU_date($date, $filter)
{
    $d0 = date('Y-m-d', strtotime($date));
    if (!isEmpty($filter[0])) {
        $filter[0] = date('Y-m-d', strtotime($filter[0]));
    } else {
        $filter[0] = '';
    }
    if (!isEmpty($filter[1])) {
        $filter[1] = date('Y-m-d', strtotime($filter[1]));
    } else {
        $filter[1] = '';
    }
    $ss1 = sql_call("SELECT id FROM edomiProject.editAgendaData WHERE ('" . $filter[2] . "'=0 OR '" . $filter[1] . "'='' OR '" . $filter[1] . "'>='" . $d0 . "') AND
		(
			('" . $filter[2] . "'=0 AND '" . $filter[0] . "'='" . $d0 . "')
		OR
			('" . $filter[2] . "'>0 AND '" . $filter[3] . "'=0 AND '" . $filter[0] . "'<='" . $d0 . "' AND (DATEDIFF('" . $d0 . "','" . $filter[0] . "') MOD '" . $filter[2] . "')=0)
		OR
			('" . $filter[2] . "'>0 AND '" . $filter[3] . "'=1 AND '" . $filter[0] . "'<='" . $d0 . "' AND (DATEDIFF('" . $d0 . "','" . $filter[0] . "') MOD ('" . $filter[2] . "'*7))=0)
		OR
			('" . $filter[2] . "'>0 AND '" . $filter[3] . "'=2 AND '" . $filter[0] . "'<='" . $d0 . "' AND ((TIMESTAMPDIFF(MONTH,'" . $filter[0] . "','" . $d0 . "') MOD '" . $filter[2] . "'=0) AND (DATE_ADD('" . $filter[0] . "',INTERVAL TIMESTAMPDIFF(MONTH,'" . $filter[0] . "','" . $d0 . "') MONTH)='" . $d0 . "')))
		OR
			('" . $filter[2] . "'>0 AND '" . $filter[3] . "'=3 AND '" . $filter[0] . "'<='" . $d0 . "' AND ((TIMESTAMPDIFF(YEAR,'" . $filter[0] . "','" . $d0 . "') MOD '" . $filter[2] . "'=0) AND (DATE_ADD('" . $filter[0] . "',INTERVAL TIMESTAMPDIFF(YEAR,'" . $filter[0] . "','" . $d0 . "') YEAR)='" . $d0 . "')))
		)
	");
    if ($nn = sql_result($ss1)) {
        sql_close($ss1);
        return true;
    }
    sql_close($ss1);
    return false;
}

function getSysInfo($sysId)
{
    $n = sql_getValues('edomiLive.RAMsysInfo', 'value', 'id=' . $sysId);
    if ($n !== false) {
        return $n['value'];
    }
    return false;
}

function setSysInfo($sysId, $sysValue)
{
    return sql_call("UPDATE edomiLive.RAMsysInfo SET value=" . $sysValue . " WHERE (id=" . $sysId . ")");
}

function getEdomiStatus()
{
    $n = getSysInfo(1);
    $procStatus = procStatus_getVar(1, 1);
    if ($procStatus === false || $n === false || $n == 0 || $n == -1) {
        return -1;
    } else {
        if ($n == -2 || $n == 2 || $n == 1) {
            if (!is_numeric($procStatus) || !file_exists('/proc/' . $procStatus)) {
                return -1;
            }
        }
        if ($n == -3) {
            return 0;
        }
        if ($n == -2) {
            return 1;
        }
        if ($n == 1) {
            return 2;
        }
        if ($n == 2) {
            return 3;
        }
    }
    return -1;
}

function get_clientId()
{
    $r = '';
    if (file_exists(MAIN_PATH . '/clientid.edomi')) {
        $tmp = file_get_contents(MAIN_PATH . '/clientid.edomi');
        if (strlen($tmp) == 64) {
            $r = $tmp;
        }
    }
    if (isEmpty($r)) {
        $tmp = global_serverIP . date('YmdHis') . rand(0, 9999999999) . global_version . global_mailLogin;
        $r = substr(hash('sha256', $tmp), 0, 64);
        file_put_contents(MAIN_PATH . '/clientid.edomi', $r);
    }
    return $r;
}

function procStatus_getTrigger($timer = null)
{
    if (isEmpty($timer) || ($timer + 0.25) < getMicrotime()) {
        $r[0] = true;
        $r[1] = getMicrotime();
    } else {
        $r[0] = false;
        $r[1] = $timer;
    }
    return $r;
}

function procStatus_getControl($varid)
{
    $r = false;
    $ss1 = sql_call("SELECT s" . $varid . " AS anz1 FROM edomiLive.RAMsysProc WHERE id=9");
    if ($n = sql_result($ss1)) {
        $r = $n['anz1'];
    }
    sql_close($ss1);
    return $r;
}

function procStatus_setControl($varid, $value)
{
    sql_call("UPDATE edomiLive.RAMsysProc SET s" . $varid . "=" . sql_encodeValue($value, true) . " WHERE id=9");
}

function procStatus_getData($id)
{
    $r = false;
    $ss1 = sql_call("SELECT * FROM edomiLive.RAMsysProc WHERE id=" . $id);
    if ($n = sql_result($ss1)) {
        $r = array();
        for ($t = 0; $t <= 20; $t++) {
            array_push($r, $n['s' . $t]);
        }
    }
    sql_close($ss1);
    return $r;
}

function procStatus_setData($id, $dataArray)
{
    $r = false;
    $n = '';
    foreach ($dataArray as $k => $v) {
        $n .= "s" . $k . "=" . sql_encodeValue($v, true) . ',';
    }
    $n = rtrim($n, ',');
    if (!isEmpty($n)) {
        $r = sql_call("UPDATE edomiLive.RAMsysProc SET " . $n . " WHERE id=" . $id);
    }
    if ($r !== false) {
        return true;
    } else {
        return false;
    }
}

function procStatus_getVar($id, $varid)
{
    $r = false;
    $ss1 = sql_call("SELECT s" . $varid . " AS anz1 FROM edomiLive.RAMsysProc WHERE id=" . $id);
    if ($n = sql_result($ss1)) {
        $r = $n['anz1'];
    }
    sql_close($ss1);
    return $r;
}

function procStatus_setVar($id, $varid, $value)
{
    sql_call("UPDATE edomiLive.RAMsysProc SET s" . $varid . "=" . sql_encodeValue($value, true) . " WHERE id=" . $id);
}

function procStatus_wait($id, $varid, $value, $timeout, $pid)
{
    $t = getMicrotime();
    do {
        $procData = procStatus_getData($id);
        if ($pid !== false && (!checkProcess($pid))) {
            return false;
        }
        usleep(1000 * 10);
    } while ($procData[$varid] != $value && ((getMicrotime() - $t) < $timeout));
    if ($procData[$varid] == $value) {
        return true;
    }
    return false;
}

function procStatus_waitReady($id, $timeout)
{
    $t = getMicrotime();
    do {
        $procData = procStatus_getData($id);
        usleep(1000 * 10);
    } while ($procData[19] != 1 && $procData[19] != 2 && ((getMicrotime() - $t) < $timeout));
    if ($procData[19] == 1 || $procData[19] == 2) {
        return true;
    }
    return false;
}

function procStatus_quit($id, $pid)
{
    if (checkProcess($pid)) {
        procStatus_setControl($id, 1);
        $t = 0;
        while (checkProcess($pid) && $t < (10 * 10)) {
            usleep(100 * 1000);
            $t++;
        }
    }
    return (!checkProcess($pid));
}

function procStatus_getProcValues(&$procData, &$last)
{
    $tmp = getMicrotime();
    if (($tmp - $last) >= 30) {
        $last = $tmp;
        $procData[20] = round(memory_get_usage(true) / 1024 / 1024, 2);
    }
}

function queueCmd($cmd, $cmdid, $cmdvalue = '')
{
    sql_call("INSERT INTO edomiLive.RAMcmdQueue (cmd,status,cmdid,cmdvalue) VALUES (" . $cmd . ",0," . $cmdid . ",'" . sql_encodeValue($cmdvalue) . "')");
}

function getFilesCount($filepath)
{
    $r = 0;
    $n = glob($filepath, GLOB_NOSORT);
    foreach ($n as $pathFn) {
        if (is_file($pathFn)) {
            $r++;
        }
    }
    return $r;
}

function isMounted($filepath)
{
    system('mountpoint -q "' . $filepath . '"', $tmp);
    if ($tmp === 0) {
        return true;
    }
    return false;
}

function getHddSpace($dev)
{
    $n[0] = truncFloat(disk_total_space($dev) / (1000 * 1000));
    $n[1] = truncFloat(disk_free_space($dev) / (1000 * 1000));
    if ($n[0] > 0) {
        $n[2] = truncFloat(($n[1] * 100) / $n[0]);
    } else {
        $n[2] = 0;
    }
    return $n;
}

function getFileEncoding($filepath)
{
    $n = array();
    exec('file -i "' . $filepath . '"', $n);
    if (isset($n[0])) {
        $tmp = explode('charset=', $n[0]);
        return (isset($tmp[1]) ? $tmp[1] : null);
    }
    return null;
}

function getFileSize($fn)
{
    if (file_exists($fn)) {
        clearstatcache();
        return filesize($fn);
    }
    return 0;
}

function getFolderSize($filepath)
{
    $r = 0;
    clearstatcache();
    $n = glob($filepath, GLOB_NOSORT);
    foreach ($n as $pathFn) {
        if (is_file($pathFn)) {
            $r += filesize($pathFn);
        }
    }
    return $r;
}

function getFileLines($fn)
{
    if (file_exists($fn)) {
        clearstatcache();
        return intval(exec('wc -l "' . $fn . '"'));
    }
    return 0;
}

function getFileMd5($fn)
{
    return md5_file($fn);
}

function deleteFiles($fn)
{
    exec('rm -f ' . $fn);
}

function createInfoFile($fn, $arr)
{
    $ts = getTimestamp();
    $n = implode(SEPARATOR1, $arr);
    $fh = fopen($fn, 'w');
    fwrite($fh, $n . SEPARATOR1 . $ts[0] . ' ' . $ts[1]);
    fclose($fh);
}

function readInfoFile($fn)
{
    if (file_exists($fn)) {
        $n = file_get_contents($fn);
        $r = explode(SEPARATOR1, $n);
        return $r;
    }
    return false;
}

function getArchivCamFilename($archivId, $camId, $ts1, $ts2)
{
    $n = str_replace(':', '', $ts1);
    $n = str_replace('-', '', $n);
    $n = str_replace(' ', '', $n);
    return 'archiv' . $archivId . '-cam' . $camId . '-' . $n . sprintf("%06d", $ts2);
}

function urlExists($url)
{
    $ctx = stream_context_create(array('http' => array('timeout' => 10)));
    $r = file_get_contents($url, false, $ctx, 0, 1);
    if ($r !== false) {
        return true;
    } else {
        return false;
    }
}

function urlDownload($url, $destFn, $infoFile = null, $srcSize = 0)
{
    $dstSize = 0;
    $f1 = fopen($url, 'rb');
    if ($f1 !== false) {
        $t = 0;
        $f2 = fopen(MAIN_PATH . '/www/data/tmp/' . $destFn, 'w');
        while (!feof($f1)) {
            $n = fread($f1, 8192);
            $bytes = fwrite($f2, $n);
            if ($bytes !== false) {
                $dstSize += $bytes;
            }
            if (!isEmpty($infoFile) && getMicrotime() > ($t + 1)) {
                $t = getMicrotime();
                createInfoFile(MAIN_PATH . '/www/data/tmp/' . $infoFile, array($srcSize, $dstSize));
            }
        }
        if (!isEmpty($infoFile)) {
            createInfoFile(MAIN_PATH . '/www/data/tmp/' . $infoFile, array($srcSize, $dstSize));
        }
        fclose($f2);
        fclose($f1);
        return true;
    }
    return false;
}

function isEmpty($n)
{
    return (!strlen($n));
}

function truncFloat($n)
{
    return intval(strval($n));
}

function string_cutout($n, $s1, $s2)
{
    $x1 = strpos($n, $s1);
    if ($x1 !== false) {
        $x1 += strlen($s1);
        $x2 = strpos($n, $s2, $x1);
        if ($x2 !== false) {
            return substr($n, $x1, ($x2 - $x1));
        }
    }
    return false;
}

function string_cutoutInverse($n, $s1, $s2)
{
    $x1 = strpos($n, $s1);
    if ($x1 !== false) {
        $x2 = strpos($n, $s2, $x1 + strlen($s1));
        if ($x2 !== false) {
            return substr($n, 0, $x1) . substr($n, $x2 + strlen($s2));
        }
    }
    return $n;
}

function writeToTmpLog($level, $n, $err = false)
{
    $line = '';
    if ($level == 0) {
        if ($err) {
            $line = "<div style='color:#ff0000;'>&bull; <b>" . $n . "</b></div>";
        } else {
            $line = "<div>&bull; " . $n . "</div>";
        }
    } else if ($level == 1) {
        if ($err) {
            $line = "<div style='padding-left:10px; color:#ff5959;'>&gt; " . $n . "</div>";
        } else {
            $line = "<div style='padding-left:10px; color:#595959;'>&gt; " . $n . "</div>";
        }
    } else if ($level == 11) {
        if ($err) {
            $line = "<div style='padding-left:10px; color:#ff5959;'>&gt; " . $n . "</div>";
        } else {
            $line = "<div style='padding-left:10px; color:#a0a0a0;'>&gt; " . $n . "</div>";
        }
    } else if ($level == 2) {
        if ($err) {
            $line = "<div style='padding-left:20px; color:#ff5959;'>&gt; " . $n . "</div>";
        } else {
            $line = "<div style='padding-left:20px; color:#595959;'>&gt; " . $n . "</div>";
        }
    }
    if (!isEmpty($line)) {
        file_put_contents(MAIN_PATH . '/www/data/tmp/tmp_log.txt', $line, FILE_APPEND);
    }
}

function echoTmpLog()
{
    echo file_get_contents(MAIN_PATH . '/www/data/tmp/tmp_log.txt');
}

function clearTmpLog()
{
    deleteFiles(MAIN_PATH . '/www/data/tmp/tmp_log.txt');
}

function dbRoot_getAllow($id)
{
    $tmp = parseFolderId($id);
    $id = $tmp[0];
    $folder = sql_getValues('edomiProject.editRoot', 'allow,rootid', 'id=' . $id);
    if ($folder !== false) {
        if ($id == $folder['rootid']) {
            if (!isEmpty($folder['allow'])) {
                return $folder['allow'];
            }
        } else {
            $r = sql_getValue('edomiProject.editRoot', 'allow', 'id=' . $folder['rootid']);
            if (!isEmpty($r)) {
                return $r;
            }
        }
    }
    return false;
}

function dbRoot_getRootId($id)
{
    $tmp = explode('/', trim(dbRoot_getRootPath($id), '/'));
    if (count($tmp) > 0) {
        $r = $tmp[count($tmp) - 1];
        if ($r > 0) {
            return $r;
        }
    }
    return false;
}

function dbRoot_getRootPath($id)
{
    $r = '/';
    $ss1 = sql_call("SELECT path FROM edomiProject.editRoot WHERE (id=" . $id . ")");
    if ($n = sql_result($ss1)) {
        $tmp = explode('/', $n['path'] . $id);
        for ($t = 1; $t < count($tmp); $t++) {
            if ($tmp[$t] > 0 && $tmp[$t] < 1000) {
                $r .= $tmp[$t] . '/';
            } else {
                break;
            }
        }
    }
    sql_close($ss1);
    return $r;
}

function dbRoot_getFullPath($id, $visuId = 0)
{
    $r = '';
    $ss1 = sql_call("SELECT path FROM edomiProject.editRoot WHERE (id=" . $id . ")");
    if ($n = sql_result($ss1)) {
        $tmp = explode('/', $n['path'] . $id);
        for ($t = 1; $t < count($tmp); $t++) {
            $r .= sql_getValue('edomiProject.editRoot', 'name', 'id=' . $tmp[$t]) . '/';
            if ($visuId >= 1 && $tmp[$t] == 22) {
                $visu = sql_getValues('edomiProject.editVisu', 'id,name', 'id=' . $visuId);
                if ($visu !== false) {
                    $r .= $visu['name'] . ' (' . $visu['id'] . ')' . '/';
                }
            }
        }
    }
    sql_close($ss1);
    return $r;
}

function dbRoot_getOrdnerString($id, $mode = false, $dbAlias = '')
{
    $path = sql_getValue('edomiProject.editRoot', 'path', 'id=' . $id);
    $path = ltrim($path . $id, '/');
    $tmp = explode('/', $path);
    for ($t = count($tmp) - 1; $t >= 0; $t--) {
        if ($tmp[$t] < 1000) {
            $sort = sql_getValues('edomiProject.editRoot', 'sortid,sortcolumns', "id=" . $tmp[$t] . " AND sortid<>0 AND sortcolumns<>''");
            if ($sort !== false) {
                if ($mode) {
                    $r = array($tmp[$t], $sort['sortcolumns'], $sort['sortid']);
                    return $r;
                } else {
                    $n = explode(',', $sort['sortcolumns']);
                    $nn = explode('/', $n[abs($sort['sortid']) - 1]);
                    if ($nn[0] == 'ga') {
                        return "CAST(SUBSTRING_INDEX(" . $dbAlias . "ga, '/', 1) AS UNSIGNED) " . (($sort['sortid'] > 0) ? "ASC" : "DESC") . ",CAST(SUBSTRING_INDEX(SUBSTRING(" . $dbAlias . "ga,LOCATE('/'," . $dbAlias . "ga)+1), '/', 1) AS UNSIGNED) " . (($sort['sortid'] > 0) ? "ASC" : "DESC") . ",CAST(SUBSTRING_INDEX(" . $dbAlias . "ga, '/', -1) AS UNSIGNED) " . (($sort['sortid'] > 0) ? "ASC" : "DESC");
                    } else {
                        if ($nn[0] != 'id') {
                            return $dbAlias . $nn[0] . (($sort['sortid'] > 0) ? " ASC" : " DESC") . ',' . $dbAlias . 'id' . (($sort['sortid'] > 0) ? " ASC" : " DESC");
                        } else {
                            return $dbAlias . $nn[0] . (($sort['sortid'] > 0) ? " ASC" : " DESC");
                        }
                    }
                }
            }
        }
    }
    if ($mode) {
        return false;
    } else {
        return $dbAlias . "id ASC";
    }
}

function db_convertTableName($mode, $table)
{
    if (substr($table, 0, 4) == 'edit') {
        $table = substr($table, 4, strlen($table) - 4);
    }
    if ($mode == 0) {
        return 'edomiProject.edit' . ucfirst($table);
    } else if ($mode == 1) {
        return 'edomiLive.' . lcfirst($table);
    }
}

function visu_getElementDesignData($live, $mode, $elementId, $koValue, $koDPT)
{
    global $global_dptData;
    $style = false;
    if ($mode >= 0) {
        if ($mode == 0) {
            if (!isEmpty($koDPT)) {
                if ($global_dptData[$koDPT][2] == 3 || $global_dptData[$koDPT][2] == 4) {
                    $mode = 1;
                } else if ($global_dptData[$koDPT][2] == 0) {
                    if (is_numeric($koValue)) {
                        $mode = 2;
                    } else {
                        $mode = 1;
                    }
                } else {
                    $mode = 2;
                }
            }
        }
        if ($mode == 1) {
            if ($live) {
                $style = sql_getValues('edomiLive.visuElementStyle', '*', "targetid=" . $elementId . " AND styletyp=1 AND '" . sql_encodeValue($koValue) . "'>=fromvalue AND '" . sql_encodeValue($koValue) . "'<=tovalue");
            } else {
                if (sql_getCount('edomiProject.editVisuElementDesignDef', 'id>0') > 0) {
                    $style = sql_getValues('edomiProject.editVisuElementDesign AS a,edomiProject.editVisuElementDesignDef AS b', 'a.*', "a.targetid=" . $elementId . " AND a.styletyp=1 AND (
						(a.defid=0 AND
							'" . sql_encodeValue($koValue) . "'>=a.s1 AND '" . sql_encodeValue($koValue) . "'<=a.s2
						)
						OR
						(b.id=a.defid AND
							((a.s1 IS NOT NULL AND '" . sql_encodeValue($koValue) . "'>=a.s1) OR (a.s1 IS NULL AND '" . sql_encodeValue($koValue) . "'>=b.s1))
							AND
							((a.s2 IS NOT NULL AND '" . sql_encodeValue($koValue) . "'<=a.s2) OR (a.s2 IS NULL AND '" . sql_encodeValue($koValue) . "'<=b.s2))
						)
					)");
                } else {
                    $style = sql_getValues('edomiProject.editVisuElementDesign AS a', 'a.*', "a.targetid=" . $elementId . " AND a.styletyp=1 AND (
						(a.defid=0 AND
							'" . sql_encodeValue($koValue) . "'>=a.s1 AND '" . sql_encodeValue($koValue) . "'<=a.s2
						)
					)");
                }
            }
        } else if ($mode == 2) {
            if ($live) {
                $style = sql_getValues('edomiLive.visuElementStyle', '*', "targetid=" . $elementId . " AND styletyp=1 AND " . sql_encodeValue($koValue) . ">=fromvalue AND " . sql_encodeValue($koValue) . "<=tovalue");
            } else {
                if (sql_getCount('edomiProject.editVisuElementDesignDef', 'id>0') > 0) {
                    $style = sql_getValues('edomiProject.editVisuElementDesign AS a,edomiProject.editVisuElementDesignDef AS b', 'a.*', "a.targetid=" . $elementId . " AND a.styletyp=1 AND (
						(a.defid=0 AND
							" . sql_encodeValue($koValue) . ">=a.s1 AND " . sql_encodeValue($koValue) . "<=a.s2
						)
						OR
						(b.id=a.defid AND
							((a.s1 IS NOT NULL AND " . sql_encodeValue($koValue) . ">=a.s1) OR (a.s1 IS NULL AND " . sql_encodeValue($koValue) . ">=b.s1))
							AND
							((a.s2 IS NOT NULL AND " . sql_encodeValue($koValue) . "<=a.s2) OR (a.s2 IS NULL AND " . sql_encodeValue($koValue) . "<=b.s2))
						)
					)");
                } else {
                    $style = sql_getValues('edomiProject.editVisuElementDesign AS a', 'a.*', "a.targetid=" . $elementId . " AND a.styletyp=1 AND (
						(a.defid=0 AND
							" . sql_encodeValue($koValue) . ">=a.s1 AND " . sql_encodeValue($koValue) . "<=a.s2
						)
					)");
                }
            }
        }
    }
    if ($style === false) {
        $isStatic = true;
        if ($live) {
            $style = sql_getValues('edomiLive.visuElementStyle', '*', 'targetid=' . $elementId . ' AND styletyp=0');
        } else {
            $style = sql_getValues('edomiProject.editVisuElementDesign', '*', 'targetid=' . $elementId . ' AND styletyp=0');
        }
    } else {
        $isStatic = false;
    }
    if (!$live && $style !== false) {
        if ($style['defid'] > 0) {
            $tmp = sql_getValues('edomiProject.editVisuElementDesignDef', '*', 'id=' . $style['defid']);
            if ($tmp !== false) {
                for ($t = 1; $t <= 48; $t++) {
                    if (!isEmpty($style['s' . $t])) {
                        $tmp['s' . $t] = $style['s' . $t];
                    }
                }
                $tmp["styletyp"] = $style["styletyp"];
                $style = $tmp;
                if ($isStatic) {
                    $style['s11'] = '';
                }
            }
        }
    }
    return $style;
}

function visu_getElementStyleCss($item, $vseDef, $design, $previewMode, $activation)
{
    $head = array(array());
    $cssVar = '';
    $anim_p1 = array(0 => 'linear', 1 => 'ease', 2 => 'ease-in', 3 => 'ease-out', 4 => 'ease-in-out');
    $anim_p2 = array(0 => 'normal', 1 => 'reverse', 2 => 'alternate', 3 => 'alternate-reverse');
    $anim_p3 = array(0 => 'none', 1 => 'forwards', 2 => 'backwards', 3 => 'both');
    if ($activation && $item['controltyp'] == 0) {
        return array('position:absolute;left:0;top:0;width:1;height:1;display:none;', '', $head);
    }
    if ($activation) {
        $path_img = '../data/liveproject/visu/img';
        $path_etc = '../data/liveproject/visu/etc';
    } else {
        $path_img = '../data/project/visu/img';
        $path_etc = '../data/project/visu/etc';
    }
    if ($vseDef !== false) {
        for ($t = 1; $t <= 20; $t++) {
            if ($vseDef['var' . $t . 'root'] == 150 && $item['var' . $t] > 0 && ($n = sql_getValues('edomiProject.editVisuFont', '*', 'id=' . $item['var' . $t]))) {
                if ($activation) {
                    class_projectActivation_visuAddMeta($item['visuid'], 150, $n['id']);
                } else {
                    if ($n['fonttyp'] == 0) {
                        $head[0][$n['id']] = "@font-face {font-family:font" . $n['id'] . "; src:local(" . $n['fontname'] . ");}";
                    } else {
                        $head[0][$n['id']] = "@font-face {font-family:font" . $n['id'] . "; font-style:" . (($n['fontstyle'] == 0) ? 'normal' : 'italic') . "; font-weight:" . (($n['fontweight'] == 0) ? 'normal' : 'bold') . "; src:url('" . $path_etc . '/font-' . $n['id'] . '.ttf?' . $n['ts'] . "') format('truetype');}";
                    }
                }
                $cssVar .= "--var" . $t . ":font" . $n['id'] . ";";
            }
            if ($vseDef['var' . $t . 'root'] == 27 && $item['var' . $t] > 0 && ($n = sql_getValues('edomiProject.editVisuAnim', '*', 'id=' . $item['var' . $t]))) {
                if ($activation) {
                    class_projectActivation_visuAddMeta($item['visuid'], 27, $n['id']);
                } else {
                    $head[1][$n['id']] = "@-webkit-keyframes anim" . $n['id'] . " {" . $n['keyframes'] . "}";
                }
                $cssVar .= "--var" . $t . ":anim" . $n['id'] . " 1s " . $anim_p1[$n['timing']] . " " . floatVal($n['delay']) . "s 1 " . $anim_p2[$n['direction']] . " " . $anim_p3[$n['fillmode']] . ";";
            }
            if ($vseDef['var' . $t . 'root'] == 28 && $item['var' . $t] > 0 && ($n = sql_getValues('edomiProject.editVisuImg', '*', 'id=' . $item['var' . $t]))) {
                if ($activation) {
                    class_projectActivation_visuAddMeta($item['visuid'], 28, $n['id']);
                }
                $cssVar .= "--var" . $t . ":'" . $path_img . "/img-" . $n['id'] . "." . $n['suffix'] . "?" . $n['ts'] . "';";
            }
            if ($vseDef['var' . $t . 'root'] == 29 && $item['var' . $t] > 0 && ($n = sql_getValues('edomiProject.editVisuSnd', '*', 'id=' . $item['var' . $t]))) {
                if ($activation) {
                    class_projectActivation_visuAddMeta($item['visuid'], 29, $n['id']);
                }
                $cssVar .= "--var" . $t . ":'" . $path_etc . "/snd-" . $n['id'] . ".mp3';";
            }
            if ($vseDef['var' . $t . 'root'] == 26 && $item['var' . $t] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $item['var' . $t]))) {
                $cssVar .= "--var" . $t . ":" . $n['color'] . ";";
            }
            if ($vseDef['var' . $t . 'root'] == 25 && $item['var' . $t] > 0 && ($n = sql_getValues('edomiProject.editVisuBGcol', 'color', 'id=' . $item['var' . $t]))) {
                $cssVar .= "--var" . $t . ":" . $n['color'] . ";";
            }
        }
    }
    $css = 'position:absolute;';
    $css .= 'overflow:hidden;';
    $css .= 'z-index:' . $item['zindex'] . ';';
    $css .= 'box-sizing:border-box;';
    $css .= 'left:' . $item['xpos'] . 'px; left:-webkit-calc(' . $item['xpos'] . 'px + ' . $design['s3'] . 'px);';
    $css .= 'top:' . $item['ypos'] . 'px; top:-webkit-calc(' . $item['ypos'] . 'px + ' . $design['s4'] . 'px);';
    $css .= 'width:' . $item['xsize'] . 'px; width:-webkit-calc(' . $item['xsize'] . 'px + ' . $design['s5'] . 'px);';
    $css .= 'height:' . $item['ysize'] . 'px; height:-webkit-calc(' . $item['ysize'] . 'px + ' . $design['s6'] . 'px);';
    $cssVar .= '--dx:' . $design['s3'] . ';';
    $cssVar .= '--dy:' . $design['s4'] . ';';
    $cssVar .= '--sx:' . $design['s5'] . ';';
    $cssVar .= '--sy:' . $design['s6'] . ';';
    if (!isEmpty($design['s7'])) {
        $css .= '-webkit-transform:rotate(' . $design['s7'] . 'deg);';
    }
    $cssVar .= '--rt:' . $design['s7'] . ';';
    if ($previewMode || $activation) {
        if (!isEmpty($design['s8'])) {
            $css .= 'opacity:' . $design['s8'] . ';';
        }
    }
    if ($design['s13'] > 0 && ($n = sql_getValues('edomiProject.editVisuFont', '*', 'id=' . $design['s13']))) {
        $css .= 'font-family:font' . $n['id'] . ';';
        if ($activation) {
            class_projectActivation_visuAddMeta($item['visuid'], 150, $n['id']);
        } else {
            if ($n['fonttyp'] == 0) {
                $head[0][$n['id']] = "@font-face {font-family:font" . $n['id'] . "; src:local(" . $n['fontname'] . ");}";
            } else {
                $head[0][$n['id']] = "@font-face {font-family:font" . $n['id'] . "; font-style:" . (($n['fontstyle'] == 0) ? 'normal' : 'italic') . "; font-weight:" . (($n['fontweight'] == 0) ? 'normal' : 'bold') . "; src:url('" . $path_etc . '/font-' . $n['id'] . '.ttf?' . $n['ts'] . "') format('truetype');}";
            }
        }
    } else {
        $css .= 'font-family:' . global_visuFont . ';';
    }
    if (!isEmpty($design['s14'])) {
        $css .= 'font-size:' . $design['s14'] . 'px;';
    } else {
        $css .= 'font-size:' . global_visuFontSize . 'px;';
    }
    if ($design['s16'] == '1') {
        $css .= 'font-style:normal;';
    }
    if ($design['s16'] == '2') {
        $css .= 'font-style:italic;';
    }
    if ($design['s17'] == '1') {
        $css .= 'font-weight:normal;';
    }
    if ($design['s17'] == '2') {
        $css .= 'font-weight:bold;';
    }
    if ($design['s18'] == '1') {
        $css .= 'text-align:left;';
    }
    if ($design['s18'] == '2') {
        $css .= 'text-align:center;';
    }
    if ($design['s18'] == '3') {
        $css .= 'text-align:right;';
    }
    if ($design['s18'] == '4') {
        $css .= 'text-align:justify;';
    }
    if (!isEmpty($design['s12']) && ($design['s18'] == '1' || $design['s18'] == '2' || $design['s18'] == '4')) {
        $css .= 'padding-left:' . $design['s12'] . 'px;';
    }
    if (!isEmpty($design['s12']) && ($design['s18'] == '3' || $design['s18'] == '2' || $design['s18'] == '4')) {
        $css .= 'padding-right:' . $design['s12'] . 'px;';
    }
    if ($design['s15'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s15']))) {
        $css .= 'color:' . $n['color'] . ';';
        $cssVar .= '--fgc0:' . $n['color'] . ';';
    } else {
        $css .= 'color:' . global_visu_defaultFgColor . ';';
        $cssVar .= '--fgc0:' . global_visu_defaultFgColor . ';';
    }
    if (!isEmpty($design['s19']) && !isEmpty($design['s20']) && !isEmpty($design['s21']) && $design['s22'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s22']))) {
        $css .= 'text-shadow:' . $design['s19'] . 'px ' . $design['s20'] . 'px ' . $design['s21'] . 'px ' . $n['color'] . ';';
    }
    if (!isEmpty($design['s23'])) {
        $css .= 'border-top-left-radius:' . $design['s23'] . 'px;';
    }
    if (!isEmpty($design['s24'])) {
        $css .= 'border-top-right-radius:' . $design['s24'] . 'px;';
    }
    if (!isEmpty($design['s25'])) {
        $css .= 'border-bottom-right-radius:' . $design['s25'] . 'px;';
    }
    if (!isEmpty($design['s26'])) {
        $css .= 'border-bottom-left-radius:' . $design['s26'] . 'px;';
    }
    $tmp = 'none';
    if ($design['s32'] == '1') {
        $tmp = 'solid';
    }
    if ($design['s32'] == '2') {
        $tmp = 'dotted';
    }
    if ($design['s32'] == '3') {
        $tmp = 'dashed';
    }
    if ($design['s27'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s27']))) {
        $css .= 'border-left:' . $design['s31'] . 'px ' . $tmp . ' ' . $n['color'] . ';';
    }
    if ($design['s28'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s28']))) {
        $css .= 'border-top:' . $design['s31'] . 'px ' . $tmp . ' ' . $n['color'] . ';';
    }
    if ($design['s29'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s29']))) {
        $css .= 'border-right:' . $design['s31'] . 'px ' . $tmp . ' ' . $n['color'] . ';';
    }
    if ($design['s30'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s30']))) {
        $css .= 'border-bottom:' . $design['s31'] . 'px ' . $tmp . ' ' . $n['color'] . ';';
    }
    if ($design['s37'] > 0 && !isEmpty($design['s33']) && !isEmpty($design['s34']) && !isEmpty($design['s35']) && !isEmpty($design['s36']) && !isEmpty($design['s38']) && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s37']))) {
        $css .= 'box-shadow:' . $design['s33'] . 'px ' . $design['s34'] . 'px ' . $design['s35'] . 'px ' . $design['s36'] . 'px ' . $n['color'] . (($design['s38'] == '2') ? ' inset' : '') . ';';
    }
    if ($previewMode || $activation) {
        if (!$design['s41'] > 0) {
            $design['s41'] = 'infinite';
        }
        if ($design['s39'] > 0 && !isEmpty($design['s40']) && !isEmpty($design['s41']) && ($n = sql_getValues('edomiProject.editVisuAnim', '*', 'id=' . $design['s39']))) {
            $css .= '-webkit-animation:anim' . $n['id'] . ' ' . $design['s40'] . 's ' . $anim_p1[$n['timing']] . ' ' . floatVal($n['delay']) . 's ' . $design['s41'] . ' ' . $anim_p2[$n['direction']] . ' ' . $anim_p3[$n['fillmode']] . ';';
            if ($activation) {
                class_projectActivation_visuAddMeta($item['visuid'], 27, $n['id']);
            } else {
                $head[1][$n['id']] = "@-webkit-keyframes anim" . $n['id'] . " {" . $n['keyframes'] . "}";
            }
        } else {
            $css .= '-webkit-animation:none;';
        }
    }
    $tmp = '';
    if ($design['s10'] > 0 && ($n = sql_getValues('edomiProject.editVisuImg', '*', 'id=' . $design['s10']))) {
        $tmp .= "url('" . $path_img . "/img-" . $n['id'] . "." . $n['suffix'] . "?" . $n['ts'] . "')";
        $cssVar .= "--img0:'" . $path_img . "/img-" . $n['id'] . "." . $n['suffix'] . "?" . $n['ts'] . "';";
        if ($activation) {
            class_projectActivation_visuAddMeta($item['visuid'], 28, $n['id']);
        }
    }
    if ($design['s9'] > 0 && ($n = sql_getValues('edomiProject.editVisuBGcol', 'color', 'id=' . $design['s9']))) {
        $tmp .= ((!isEmpty($tmp)) ? ',' : '') . $n['color'];
        $cssVar .= '--bgc0:' . $n['color'] . ';';
    }
    if (!isEmpty($tmp)) {
        $css .= 'background:' . $tmp . ';';
        $css .= 'background-size:100% 100%;';
        $css .= 'background-repeat:no-repeat;';
    }
    if ($design['s42'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s42']))) {
        $cssVar .= '--fgc1:' . $n['color'] . ';';
    }
    if ($design['s43'] > 0 && ($n = sql_getValues('edomiProject.editVisuFGcol', 'color', 'id=' . $design['s43']))) {
        $cssVar .= '--fgc2:' . $n['color'] . ';';
    }
    if ($design['s44'] > 0 && ($n = sql_getValues('edomiProject.editVisuBGcol', 'color', 'id=' . $design['s44']))) {
        $cssVar .= '--bgc1:' . $n['color'] . ';';
    }
    if ($design['s45'] > 0 && ($n = sql_getValues('edomiProject.editVisuBGcol', 'color', 'id=' . $design['s45']))) {
        $cssVar .= '--bgc2:' . $n['color'] . ';';
    }
    if ($design['s46'] > 0 && ($n = sql_getValues('edomiProject.editVisuImg', '*', 'id=' . $design['s46']))) {
        $cssVar .= "--img1:'" . $path_img . "/img-" . $n['id'] . "." . $n['suffix'] . "?" . $n['ts'] . "';";
        if ($activation) {
            class_projectActivation_visuAddMeta($item['visuid'], 28, $n['id']);
        }
    }
    if ($design['s47'] > 0 && ($n = sql_getValues('edomiProject.editVisuImg', '*', 'id=' . $design['s47']))) {
        $cssVar .= "--img2:'" . $path_img . "/img-" . $n['id'] . "." . $n['suffix'] . "?" . $n['ts'] . "';";
        if ($activation) {
            class_projectActivation_visuAddMeta($item['visuid'], 28, $n['id']);
        }
    }
    if (!isEmpty($design['s48'])) {
        $design['s48'] = str_replace(chr(9), '', $design['s48']);
        $design['s48'] = str_replace(chr(10), '', $design['s48']);
        $design['s48'] = str_replace(chr(13), '', $design['s48']);
        $css .= $design['s48'];
    }
    return array($css, $cssVar, $head);
} ?>

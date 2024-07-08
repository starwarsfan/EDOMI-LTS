###[DEF]###
[name    =Meldungsarchiv]

[folderid=163]
[xsize    =250]
[ysize    =200]

[var1    =0 #root=60]
[var2    =30]
[var3    =0]
[var4    =2]
[var5    =0]
[var6    =2]
[var7    =1]
[var8    =30]
[var9    =70]
[var10    =]

[flagText        =0]
[flagKo1        =1]
[flagKo2        =0]
[flagKo3        =1]
[flagPage        =1]
[flagCmd        =1]
[flagDesign        =1]
[flagDynDesign    =1]

[captionKo1        =Steuerung (leer=ggf. Status-KO des Archivs)]
###[/DEF]###


###[PROPERTIES]###
[columns=50,50]
[row]
[var1 = root,2,'Meldungsarchiv',60]

[row=Aktualisierung]
[var5 = check,2,'','Aktualisierung per KO']

[row=Darstellung]
[var3 = select,2,'Modus','0#Listenansicht|1#Einzelmeldung']

[row=Zeitstempel]
[var4 = select,1,'Format','0#ohne|1#nur Uhrzeit|2#Datum/Uhrzeit|3#Datum/Uhrzeit/Mikrosekunden']
[var9 = select,1,'Opazität','100#100%|90#90%|80#80%|70#70%|60#60%|50#50%|40#40%|30#30%|20#20%|10#10%']

[row]
[var2 = select,2,'Nachladen','10#weitere 10 Einträge laden|20#weitere 20 Einträge laden|30#weitere 30 Einträge laden|40#weitere 40 Einträge laden|50#weitere 50 Einträge laden']

[row]
[var6 = select,2,'Abtrennung','0#keine|1#Freiraum|2#Linie']

[row]
[var10= text,2,'Kopfzeilenhöhe (px, leer=Standard)','']

[row=Formatierung]
[var7 = select,1,'Formatierungen anzeigen','0#deaktiviert|1#aktiviert']
[var8 = text,1,'Symbol: Breite (px, 0=automatisch)','']

###[/PROPERTIES]###


###[ACTIVATION.PHP]###
<?
//gaid auf das Status-KO des Archivs setzen, falls kein anderes KO angegeben wurde
$tmp = sql_getValues('edomiProject.editArchivMsg', 'outgaid', 'id=' . $item['var1'] . ' AND outgaid>0');
if ($tmp !== false) {
    sql_call("UPDATE edomiLive.visuElement SET gaid=" . $tmp['outgaid'] . " WHERE (id=" . $item['id'] . " AND (gaid=0 OR gaid IS NULL))");
}
?>
###[/ACTIVATION.PHP]###


###[EDITOR.PHP]###
<?
$property[0] = sql_getValue('edomiProject.editArchivMsg', 'name', 'id=' . $item['var1']);
?>
###[/EDITOR.PHP]###


###[EDITOR.JS]###
VSE_VSEID=function(elementId,obj,meta,property,isPreview,koValue) {

var mheight=(obj.dataset.var10!='')?obj.dataset.var10:40;

var n="
<table cellpadding='0' cellspacing='0' border='0' style='position:absolute; left:0; top:0; width:100%; height:100%;'>";
    n+="
    <tr style='height:"+mheight+"px;'>";
        n+="
        <td width='20%' align='center'>&lt;</td>
        ";
        n+="
        <td width='60%' align='center'>
            <div style='max-height:"+mheight+"px; overflow:hidden;'>"+property[0]+"</div>
        </td>
        ";
        n+="
        <td width='20%' align='center'>&gt;</td>
        ";
        n+="
    </tr>
    ";
    n+="
    <tr>
        <td colspan='3' align='center' style='border-top:1px dotted;'>"+((isPreview)?"":"<span class='app2_pseudoElement'>MELDUNGSARCHIV</span>")+"</td>
    </tr>
    ";
    n+="
</table>";
obj.innerHTML=n;

//Text immer zentrieren, kein Padding
obj.style.textAlign="center";
obj.style.padding="0";

return property[0];
}

###[/EDITOR.JS]###


###[VISU.PHP]###
<?
function PHP_VSE_VSEID($cmd, $json1, $json2)
{

    if ($cmd == 'archivInfo') {

        if ($json1['viewMode'] == 0) {
            //---------------------------------------------------------------
            //Listenansicht
            //---------------------------------------------------------------
            if ($json1['cursor'] == 0) {
                //erster Aufruf (es wurde also noch nicht nachgeladen)
                $json1['cursor'] = $json1['load']; //var2 (Default-Anzahl der Einträge beim ersten Start)
                $scrollToTop = true;
            } else {
                //es wurde nachgeladen
                $scrollToTop = false;
            }
            ?>
            var n="";
            n+="
            <table cellpadding='2' cellspacing='0' width='100%' border='0' style='table-layout:auto;'>";
                <?
                $ss1 = sql_call("SELECT * FROM edomiLive.archivMsgData WHERE (targetid=" . $json1['archivId'] . ") ORDER BY datetime DESC,ms DESC LIMIT 0," . $json1['cursor']);
                $count = 0;
                while ($n = sql_result($ss1)) {
                    $parsed = PHP_VSE_VSEID_parseMsg($json1['formatMode'], $n['msg'], $n['formatid']);
                    ?>
                    n+="
                <tr valign='top' style='<? echo $parsed[1]; ?> <? echo $parsed[2]; ?>'>";
                    n+="
                    <td align='left' width='1' style='white-space:nowrap;'>";
                        <?
                        if ($json1['timeMode'] == 1) {
                            ?>
                            n+="<span style='opacity:<? echo $json1['timeOpacity'] / 100; ?>;'><? echo sql_getTime($n['datetime']); ?></span>";
                            <?
                        } else if ($json1['timeMode'] == 2) {
                            ?>
                            n+="<span style='opacity:<? echo $json1['timeOpacity'] / 100; ?>;'><? echo sql_getDateTime($n['datetime']); ?></span>";
                            <?
                        } else if ($json1['timeMode'] == 3) {
                            ?>
                            n+="<span
                                style='opacity:<? echo $json1['timeOpacity'] / 100; ?>;'><? echo sql_getDateTime($n['datetime']); ?>.<? echo sprintf("%06d", $n['ms']); ?></span>";
                            <?
                        } else {
                            if ($json1['formatIconSize'] == 0) {
                                $json1['formatIconSize'] = 30;
                            }    //"Auto" ohne Timestamp ist nicht möglich (CSS) => 30px
                        }
                        if (!isEmpty($parsed[3])) {
                            ?>
                            n+="
                            <div <? if ($json1['timeMode'] >= 1) {
                                echo "style='margin-top:5px;'";
                            } ?>><img src='../data/liveproject/visu/img/<? echo $parsed[3]; ?>'
                                      width='<? echo(($json1['formatIconSize'] == 0) ? '100%' : $json1['formatIconSize']); ?>' height='auto' valign='middle'
                                      style='margin:0;' draggable='false'></img></div>";
                            <?
                        }
                        ?>
                        n+="
                    </td>";

                    n+="
                    <td align='left' style='word-break:break-all;'><? echo escapeString($parsed[0], 1); ?>&nbsp;</td>";
                    <?
                    if ($json1['rowMode'] == 1) {
                        ?>
                        n+="
                        <tr>
                            <td colspan='2'>
                                <div style='width:100%; height:1px;'></div>
                            </td>
                        </tr>";
                        <?
                    } else if ($json1['rowMode'] == 2) {
                        ?>
                        n+="
                        <tr>
                            <td colspan='2'>
                                <div style='width:100%; height:1px; border-bottom:1px solid; opacity:0.25;'></div>
                            </td>
                        </tr>";
                        <?
                    }
                    ?>
                    n+="</tr>";
                    <?
                    $count++;
                }

                if ($count >= $json1['cursor']) {
                    ?>
                    n+="
                    <tr>
                        <td id='e-<? echo $json1['elementId']; ?>-loadmore' colspan='2' align='center'>
                            <div style='margin:5px; padding:5px;'>&middot;&middot;&middot;</div>
                        </td>
                    </tr>";
                         n+="
                    <tr>
                        <td colspan='2'>
                            <div style='width:100%; height:1px; border-bottom:1px solid; opacity:0.25;'></div>
                        </td>
                    </tr>";
                    <?
                }
                ?>
                n+="
            </table>";
            VSE_VSEID_callbackLoaded(<? echo $json1['elementId']; ?>,n,<? echo $json1['cursor']; ?>,<? echo(($scrollToTop) ? 'true' : 'false'); ?>,"<? echo escapeString(sql_getValue('edomiLive.archivMsg', 'name', 'id=' . $json1['archivId']), 1); ?>");
            visuElement_onClick(document.getElementById("e-<? echo $json1['elementId']; ?>-loadmore"),function(veId,objId){VSE_VSEID_LoadMore(veId,<? echo $json1['load']; ?>);});
            <?
        } else {
            //---------------------------------------------------------------
            //Einzelmeldung
            //---------------------------------------------------------------
            ?>
            var n="";
            <?
            if ($json1['mode'] == 0) { //aktuellsten Eintrag (dies ist Default beim Öffnen)
                $ss1 = sql_call("SELECT * FROM edomiLive.archivMsgData WHERE (targetid=" . $json1['archivId'] . ") ORDER BY datetime DESC,ms DESC LIMIT 0,1");
            }
            if ($json1['mode'] == 1) { //Navigation: Zurück
                $ss1 = sql_call("SELECT * FROM edomiLive.archivMsgData WHERE (targetid=" . $json1['archivId'] . " AND CONCAT(datetime,LPAD(ms,6,'0'))<'" . $json1['cursor'] . "') ORDER BY datetime DESC,ms DESC LIMIT 0,1");
            }
            if ($json1['mode'] == -1) { //Navigation: Vor
                $ss1 = sql_call("SELECT * FROM edomiLive.archivMsgData WHERE (targetid=" . $json1['archivId'] . " AND CONCAT(datetime,LPAD(ms,6,'0'))>'" . $json1['cursor'] . "') ORDER BY datetime ASC,ms ASC LIMIT 0,1");
            }

            if ($n = sql_result($ss1)) {
                $parsed = PHP_VSE_VSEID_parseMsg($json1['formatMode'], $n['msg'], $n['formatid']);
                ?>
                n+="
                <table cellpadding='2' cellspacing='0' width='100%' border='0'
                       style='table-layout:auto; overflow:hidden; text-align:left; margin:0; padding:2px; <? echo $parsed[2]; ?>'>";
                    n+="
                    <tr valign='top'>";
                        n+="
                        <td width='1'>";
                            <?
                            if ($json1['timeMode'] == 1) {
                                ?>
                                n+="
                                <div
                                    style='white-space:nowrap; pointer-events:none; margin-bottom:5px; <? echo $parsed[1]; ?>; opacity:<? echo $json1['timeOpacity'] / 100; ?>;'><? echo sql_getTime($n['datetime']); ?></span></div>";
                                <?
                            } else if ($json1['timeMode'] == 2) {
                                ?>
                                n+="
                                <div
                                    style='white-space:nowrap; pointer-events:none; margin-bottom:5px; <? echo $parsed[1]; ?>; opacity:<? echo $json1['timeOpacity'] / 100; ?>;'><? echo sql_getDateTime($n['datetime']); ?></span></div>";
                                <?
                            } else if ($json1['timeMode'] == 3) {
                                ?>
                                n+="
                                <div
                                    style='white-space:nowrap; pointer-events:none; margin-bottom:5px; <? echo $parsed[1]; ?>; opacity:<? echo $json1['timeOpacity'] / 100; ?>;'><? echo sql_getDateTime($n['datetime']); ?>
                                    .<? echo sprintf("%06d", $n['ms']); ?></span></div>";
                                <?
                            }
                            ?>
                            n+="
                            <div id='e-<? echo $json1['elementId']; ?>-delmsg'
                                 style='display:inline-block; margin-bottom:5px; padding:5px; border:1px solid; border-radius:3px; width:100%; box-sizing:border-box; text-align:center; white-space:nowrap; <? echo $parsed[1]; ?>'>
                                Meldung löschen
                            </div>
                            ";
                            <?
                            if (!isEmpty($parsed[3])) {
                                ?>
                                n+="
                                <div><img src='../data/liveproject/visu/img/<? echo $parsed[3]; ?>'
                                          width='<? echo(($json1['formatIconSize'] == 0) ? '100%' : $json1['formatIconSize']); ?>' height='auto' valign='middle'
                                          style='margin:0;' draggable='false'></img></div>";
                                <?
                            }
                            ?>
                            n+="
                        </td>
                        ";
                        n+="
                        <td>";
                            n+="
                            <div style='word-break:break-all; pointer-events:none; <? echo $parsed[1]; ?>'><? echo escapeString($parsed[0], 1); ?>&nbsp;</div>
                            ";
                            n+="
                        </td>
                        ";
                        n+="
                    </tr>
                    ";
                    n+="
                </table>";

                VSE_VSEID_callbackLoaded(<? echo $json1['elementId']; ?>,n,"<? echo($n['datetime'] . sprintf("%06d", $n['ms'])); ?>",false,"<? echo escapeString(sql_getValue('edomiLive.archivMsg', 'name', 'id=' . $json1['archivId']), 1); ?>");
                visuElement_onClick(document.getElementById("e-<? echo $json1['elementId']; ?>-delmsg"),function(veId,objId){VSE_VSEID_DeleteMessage(veId,<? echo $json1['archivId']; ?>,"<? echo $n['datetime']; ?>","<? echo $n['ms']; ?>");});
                <?
            } else if ($json1['mode'] == 0) {
                ?>
                VSE_VSEID_callbackLoaded(<? echo $json1['elementId']; ?>,"","",false,"<? echo escapeString(sql_getValue('edomiLive.archivMsg', 'name', 'id=' . $json1['archivId']), 1); ?>");
                <?
            } else {
                ?>
                VSE_VSEID_callbackBound(<? echo $json1['elementId']; ?>,"<? echo escapeString(sql_getValue('edomiLive.archivMsg', 'name', 'id=' . $json1['archivId']), 1); ?>");
                <?
            }
        }
    }

    if ($cmd == 'archivDelete') {
        $ss1 = sql_call("SELECT outgaid FROM edomiLive.archivMsg WHERE (id=" . $json1['archivId'] . ")");
        if ($n = sql_result($ss1)) {
            sql_call("DELETE FROM edomiLive.archivMsgData WHERE (targetid=" . $json1['archivId'] . " AND datetime='" . $json1['ts1'] . "' AND ms=" . $json1['ts2'] . ")");
            //ggf. Archiv Status-KO setzen
            if ($n['outgaid'] > 0) {
                ?>
                setKoValue(<? echo $n['outgaid']; ?>,"<? echo sql_getCount('edomiLive.archivMsgData', 'targetid=' . $json1['archivId']); ?>");
                <?
            }
            //den ersten Eintrag anzeigen
            ?>
            VSE_VSEID_ShowInfo("<? echo $json1['elementId']; ?>",0);
            <?
        }
    }
}

function PHP_VSE_VSEID_parseMsg($mode, $msg, $formatid)
{
    $fg = '';
    $bg = '';
    $img = '';

    $tmp = explode('***', $msg, 2);
    if (count($tmp) == 2) {
        $msg = $tmp[1];
        $formatid = $tmp[0];
    }

    if ($mode == 1) {
        if ($formatid > 0) {
            $n = sql_getValues('edomiLive.visuFormat', '*', 'id="' . $formatid . '"');
            if ($n !== false) {
                if ($n['fgid'] > 0) {
                    $fg = sql_getValue('edomiLive.visuFGcol', 'color', 'id=' . $n['fgid']);
                }
                if ($n['bgid'] > 0) {
                    $bg = sql_getValue('edomiLive.visuBGcol', 'color', 'id=' . $n['bgid']);
                }
                if ($n['imgid'] > 0) {
                    if ($nn = sql_getValues('edomiLive.visuImg', '*', 'id=' . $n['imgid'])) {
                        $img = 'img-' . $nn['id'] . '.' . $nn['suffix'];
                    }
                }
            }
            if (!isEmpty($fg)) {
                $fg = 'color:' . $fg . ';';
            }
            if (!isEmpty($bg)) {
                $bg = 'background:' . $bg . ';';
            }
        }
    }

    return array($msg, $fg, $bg, $img);
}

?>
###[/VISU.PHP]###


###[VISU.JS]###
VSE_VSEID_CONSTRUCT=function(elementId,obj) {

var mheight=(obj.dataset.var10!='')?obj.dataset.var10:40;

var n="
<table cellpadding='0' cellspacing='0' border='0' style='position:absolute; left:0; top:0; width:100%; height:100%;'>";
    n+="
    <tr style='height:"+mheight+"px;'>";
        n+="
        <td width='20%' align='center' id='e-"+elementId+"-last'>&lt;</td>
        ";
        n+="
        <td width='60%' align='center' id='e-"+elementId+"-info'>
            <div id='e-"+elementId+"-infotext' style='max-height:"+mheight+"px; overflow:hidden;'></div>
        </td>
        ";
        n+="
        <td width='20%' align='center' id='e-"+elementId+"-next'>&gt;</td>
        ";
        n+="
    </tr>
    ";
    n+="
    <tr>
        <td colspan='3' align='center' style='border-top:1px solid;'>
            <div style='position:relative; height:100%;'>
                <div id='e-"+elementId+"-edit' style='position:absolute; top:0; left:0; right:0; bottom:0; overflow-x:hidden; overflow-y:auto;'></div>
            </div>
        </td>
    </tr>
    ";
    n+="
</table>";
obj.innerHTML=n;

obj.dataset.cursor="";
obj.dataset.blocked=0;

if (!(obj.dataset.var8>=1)) {obj.dataset.var8=0;}

if (visuElement_hasCommands(elementId)) {
visuElement_onClick(document.getElementById("e-"+elementId+"-edit"),function(veId,objId){visuElement_doCommands(veId);});
}

if (obj.dataset.var3==0) {
//Listenansicht
visuElement_onClick(document.getElementById("e-"+elementId+"-last"),function(veId,objId){scrollUp("e-"+veId+"-edit");});
visuElement_onClick(document.getElementById("e-"+elementId+"-info"),function(veId,objId){VSE_VSEID_ShowInfo(veId,0);});
visuElement_onClick(document.getElementById("e-"+elementId+"-next"),function(veId,objId){scrollDown("e-"+veId+"-edit");});
} else {
//Einzelmeldung
visuElement_onClick(document.getElementById("e-"+elementId+"-last"),function(veId,objId){VSE_VSEID_ShowInfo(veId,-1);});
visuElement_onClick(document.getElementById("e-"+elementId+"-info"),function(veId,objId){VSE_VSEID_ShowInfo(veId,0);});
visuElement_onClick(document.getElementById("e-"+elementId+"-next"),function(veId,objId){VSE_VSEID_ShowInfo(veId,1);});
}
}

VSE_VSEID_REFRESH=function(elementId,obj,isInit,isRefresh,isLive,isActive,koValue) {
//Text immer zentrieren, kein Padding
obj.style.textAlign="center";
obj.style.padding="0";

if (isInit || (isRefresh && obj.dataset.var5==1)) {
VSE_VSEID_ShowInfo(elementId,0);
}
}

VSE_VSEID_ShowInfo=function(elementId,mode) {
//mode: 0=Neustart, 1=Nachladen
//        bei Einzelmeldungs-Ansicht: -1=vorherigen Eintrag, 1=nachfolgenden Eintrag
var d=document.getElementById("e-"+elementId);
if (d) {
if (d.dataset.blocked==0) {
d.dataset.blocked=1;

if (mode==0) {d.dataset.cursor=0;}
visuElement_callPhp("archivInfo",{elementId:elementId,cursor:d.dataset.cursor,mode:mode,archivId:d.dataset.var1,load:d.dataset.var2,viewMode:d.dataset.var3,timeMode:d.dataset.var4,rowMode:d.dataset.var6,formatMode:d.dataset.var7,formatIconSize:d.dataset.var8,timeOpacity:d.dataset.var9},null);
}
}
}

VSE_VSEID_LoadMore=function(elementId,rows) {
var d=document.getElementById("e-"+elementId);
if (d) {
d.dataset.cursor=parseInt(d.dataset.cursor)+rows;
VSE_VSEID_ShowInfo(elementId,1);
}
}

VSE_VSEID_DeleteMessage=function(elementId,targetId,ts1,ts2) {
var d=document.getElementById("e-"+elementId);
if (d) {
visuElement_callPhp("archivDelete",{elementId:elementId,archivId:targetId,ts1:ts1,ts2:ts2},null);
}
}

VSE_VSEID_callbackLoaded=function(elementId,content,cursor,scroll,title) {
var d=document.getElementById("e-"+elementId);
if (d) {
d.dataset.blocked=0;

d.dataset.cursor=cursor;
document.getElementById("e-"+elementId+"-infotext").innerHTML=title;
document.getElementById("e-"+elementId+"-edit").innerHTML=content;
if (scroll) {scrollToTop("e-"+elementId+"-edit");}
}
}

VSE_VSEID_callbackBound=function(elementId,title) {
var d=document.getElementById("e-"+elementId);
if (d) {
document.getElementById("e-"+elementId+"-infotext").innerHTML=title;
d.dataset.blocked=0;
}
}
###[/VISU.JS]###


###[HELP]###
Das Visuelement "Meldungsarchiv" stellt den Inhalt eines
<link>konfigurierten Meldungsarchivs***1000-60</link> in der Visualisierung tabellarisch dar.

<h2>Spezifische Eigenschaften</h2>
Für weitere Einstellungen und Optionen siehe:
<link>Allgemeine Informationen zu Visuelementen***1002</link>

<ul>
    <li>Meldungsarchiv: Auswahl des
        <link>
        konfigurierten Meldungsarchivs***1000-60</link>, dessen Inhalt angezeigt werden soll
    </li>

    <li>
        Aktualisierung per KO: legt fest, ob die angezeigten Inhalte des Meldungsarchivs bei Änderung des KO1-Wertes (s.u.) aktualisiert werden soll
        <ul>
            <li>deaktiviert: die angezeigten Inhalte werden nur manuell aktualisiert (durch einen Klick auf die Titelleiste)</li>
            <li>aktiviert: die angezeigten Inhalte werden zusätzlich bei jeder KO1-Wertänderung aktualisiert</li>
        </ul>
    </li>

    <li>
        Modus:
        <ul>
            <li>Listenansicht: die Einträge werden als Auflistung angezeigt</li>
            <li>Einzelmeldung: Es wird nur ein Eintrag angezeigt (beginnend mit dem neuesten Eintrag). Die angezeigte Meldung kann mit einem Klick auf die
                Schaltfläche "Meldung löschen" dauerhaft aus dem Archiv entfernt werden.
            </li>
        </ul>
    </li>

    <li>
        Nachladen: legt fest, wieviele Einträge maximal übertragen werden (nur bei Modus "Listenansicht")
        <ul>
            <li>die Daten werden blockweise aus dem Meldungsarchiv übertragen</li>
            <li>beim ersten Aufruf des Visuelements wird die angegebene Anzahl an Einträgen geladen</li>
        </ul>
    </li>

    <li>Zeitstempel: legt ggf. die Formatierung und die Opazität des Zeitstempels eines Archiveintrags fest</li>

    <li>Abtrennung: fügt ggf. zwischen den Einträgen einen Freiraum bzw. eine Trennlinie ein (nur bei Modus "Listenansicht")</li>

    <li>Kopfzeilenhöhe: legt optional die Höhe der Kopfzeile in Pixeln fest</li>

    <li>
        Formatierung: legt fest, ob und wie
        <link>
        Formatierungen***1000-155</link> angewendet werden
        <ul>
            <li>Formatierungen anzeigen: ist diese Option deaktiviert, werden sämtliche Formatierungen ignoriert</li>
            <li>Symbol Breite: legt die Breite des Symbols in Pixeln fest, die Höhe wird automatisch angepasst (0/leer: die Symbolbreite wird an den Zeitstempel
                angepasst)
            </li>
        </ul>
    </li>
</ul>


<h2>Kommunikationsobjekte</h2>
Dieses Visuelement kann (optional) folgende Kommunikationsobjekte (KO) verwalten:

<ul>
    <li>
        KO1: Steuerung
        <ul>
            <li>dieser KO-Wert wird zur <span style="background:#e0ffe0;">Steuerung und Beschriftung</span> verwendet (Designs, Funktionen und Formeln)</li>
            <li>immer wenn das KO auf einen Wert gesetzt wird, wird das Visuelement ggf. aktualisiert (siehe "Aktualisierung per KO")</li>
        </ul>
    </li>

    <li>
        KO3: Steuerung des dynamischen Designs
        <ul>
            <li>dieser KO-Wert wird ausschließlich zur Steuerung eines
                <link>
                dynamischen Designs***1003</link> verwendet
            </li>
            <li>wenn dieses KO angegeben wurde, wird ein dynamisches Design durch dieses <i>KO3</i> gesteuert</li>
            <li>wenn dieses KO nicht angegeben wurde, wird ein dynamisches Design durch das <i>KO1</i> gesteuert</li>
        </ul>
    </li>
</ul>

<b>Hinweis:</b>
Falls KO1 nicht angegeben wurde, wird das KO1 bei einer Aktivierung automatisch das Status-KO des Meldungsarchivs verknüpft (sofern vorhanden). Bei der Verwendung des Status-KO des Meldungsarchivs wird das Visuelement bei jeder Änderung des Archivinhaltes ggf. automatisch aktualisiert.


<h2>Besonderheiten</h2>
<ul>
    <li>das Feld "Beschriftung" steht nicht zu Verfügung, bzw. wird ignoriert</li>
    <li>Designs: Innenabstand und Textausrichtung werden ignoriert</li>
    <li>Hinweis: wenn keine Seitensteuerungen/Befehle zugewiesen wurden, verhält sich dieses Visuelement dennoch nicht
        <link>
        klicktransparent***1002</link></li>
</ul>


<h2>Bedienung in der Visualisierung</h2>
Das Visuelement ist in 2 Teilbereiche gegliedert (von oben nach unten):

<ul>
    <li>
        Titelleiste:
        <ul>
            <li>hier werden Pfeil-Schaltflächen zum Blättern durch die Einträge (Scrollen), sowie der Name des
                <link>
                Meldungsarchivs***1000-60</link> angezeigt
            </li>
            <li>ein Klick auf den Namen des Meldungsarchivs aktualisiert den Inhalt des Visuelements</li>
        </ul>
    </li>

    <li>
        Archiveinträge:
        <ul>
            <li>hier werden die geladenen Einträge des Archivs angezeigt (absteigend nach Zeitstempel sortiert, d.h. die neuesten Einträge werden am Anfag der
                Auflistung angezeigt)
            </li>
            <li>In der Darstellungsart "Einzelmeldung" wird nur 1 Eintrag angezeigt, dieser Eintrag kann durch einen Klick auf die Schaltfläche "Meldung
                löschen" dauerhaft aus dem Archiv entfernt werden.
            </li>
            <li>mit einem Klick auf diesen Bereich werden alle zugewiesenen Seitensteuerungen/Befehle ausgeführt</li>
        </ul>
    </li>
</ul>
###[/HELP]###



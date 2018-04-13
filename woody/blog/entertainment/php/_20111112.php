<?php
require_once('/php/stock.php');
require_once('/php/stock/sinastockref.php');
require_once('/php/stock/yahoostockref.php');
require_once('/php/ui/referenceparagraph.php');     // EchoReferenceTable

function EchoStockPrice($bChinese)
{
	if ($bChinese)
    {
        $ref = new SinaStockReference('ACTS');
        $ref->strDescription = $ref->GetChineseName();
    }
    else
    {
        $ref = new YahooStockReference('ACTS');
        $ref->strDescription = $ref->GetEnglishName();
    }
    EchoReferenceTable(array($ref), $bChinese);
    echo '<br />'.$ref->DebugLink();
}


?>

<?php
require_once('_stock.php');
require_once('/php/ui/fundhistoryparagraph.php');

class _GradedFundGroup extends _MyStockGroup 
{
    var $bCanTradeM;
    
    // constructor
    function _GradedFundGroup($strSymbol) 
    {
        PrefetchStockData(GradedFundGetAllSymbolArray($strSymbol));
        $this->ref = new MyGradedFundReference($strSymbol);
        
        $arRef = array($this->ref->stock_ref, $this->ref->b_ref->stock_ref);
        $this->bCanTradeM = $this->ref->m_ref->stock_ref->bHasData; 
        if ($this->bCanTradeM)
        {
            $arRef[] = $this->ref->m_ref->stock_ref;     
        }

        $this->arDisplayRef = array_merge($arRef, array($this->ref, $this->ref->b_ref, $this->ref->m_ref, $this->ref->est_ref));
        parent::_MyStockGroup($arRef);
    } 
} 

// ****************************** Reference table *******************************************************

function _echoRefTableData($ref, $fund)
{
//    if ($ref)
    if ($ref->bHasData)
    {
        $strPrice = $ref->strPrice;
        $strDate = $ref->strDate;
        $strTime = $ref->strTimeHM;
        $strPercentageDisplay = $ref->GetCurrentPercentageDisplay();
        $strLink = $ref->strExternalLink;
    }
    else
    {
        $strPrice = '';
        $strDate = '';
        $strTime = '';
        $strPercentageDisplay = '';
        $strLink = $fund->GetStockSymbol();
    }
    
    if ($fund)
    {
        $strNetValue = $fund->GetCurrentPriceDisplay();
        $strLastNetValue = $fund->strPrevPrice;
        $strReferencePrice = $fund->GetPriceDisplay($fund->fFairNetValue);
    }
    else
    {
        $strNetValue = '';
        $strLastNetValue = '';
        $strReferencePrice = '';
    }
        
    echo <<<END
    <tr>
        <td class=c1>$strLink</td>
        <td class=c1>$strPrice</td>
        <td class=c1>$strDate</td>
        <td class=c1>$strTime</td>
        <td class=c1>$strPercentageDisplay</td>
        <td class=c1>$strLastNetValue</td>
        <td class=c1>$strNetValue</td>
        <td class=c1>$strReferencePrice</td>
    </tr>
END;
}

function _echoRefTable($group, $bChinese)
{
    if ($bChinese)     
    {
        $arColumn = array('代码', PRICE_DISPLAY_CN, '日期', '时间', '涨跌', '前日净值', '官方'.EST_DISPLAY_CN, '参考'.EST_DISPLAY_CN);
    }
    else
    {
        $arColumn = array('Symbol', PRICE_DISPLAY_US, 'Date', 'Time', 'Change', 'Last Net', 'Official '.EST_DISPLAY_US, 'Fair '.EST_DISPLAY_US);
    }
    
    echo <<<END
        <TABLE borderColor=#cccccc cellSpacing=0 width=640 border=1 class="text" id="reference">
        <tr>
            <td class=c1 width=80 align=center>{$arColumn[0]}</td>
            <td class=c1 width=100 align=center>{$arColumn[1]}</td>
            <td class=c1 width=100 align=center>{$arColumn[2]}</td>
            <td class=c1 width=50 align=center>{$arColumn[3]}</td>
            <td class=c1 width=70 align=center>{$arColumn[4]}</td>
            <td class=c1 width=80 align=center>{$arColumn[5]}</td>
            <td class=c1 width=80 align=center>{$arColumn[6]}</td>
            <td class=c1 width=80 align=center>{$arColumn[7]}</td>
        </tr>
END;

    $ref = $group->ref;
    _echoRefTableData($ref->est_ref, false);
    _echoRefTableData($ref->m_ref->stock_ref, $ref->m_ref);
    _echoRefTableData($ref->stock_ref, $ref);
    _echoRefTableData($ref->b_ref->stock_ref, $ref->b_ref);
    
    EchoTableEnd();
}

function _echoRefParagraph($group, $bChinese)
{
    EchoParagraphBegin($bChinese ? '数据和净值表' : 'Data and net value table');
    _echoRefTable($group, $bChinese);
    EchoParagraphEnd();
}

// ****************************** private functions *******************************************************

function _echoAdminTestParagraph($group, $bChinese)
{
    EchoParagraph($group->GetDebugString($bChinese));
}

function EchoAll($bChinese)
{
    global $group;
    $fund = $group->ref;
    $b_fund = $fund->b_ref;
    if ($group->bCanTradeM)
    {
        $m_fund = $fund->m_ref;
    }
    
    _echoRefParagraph($group, $bChinese);

    EchoFundTradingParagraph($fund, false, $bChinese);    
    EchoFundTradingParagraph($b_fund, false, $bChinese);    
    if ($group->bCanTradeM)
    {
        EchoFundTradingParagraph($m_fund, false, $bChinese);    
    }

    EchoFundHistoryParagraph($fund, 0, TABLE_COMMON_DISPLAY, $bChinese);
    EchoFundHistoryParagraph($b_fund, 0, TABLE_COMMON_DISPLAY, $bChinese);
    if ($group->bCanTradeM)
    {
        EchoFundHistoryParagraph($m_fund, 0, TABLE_COMMON_DISPLAY, $bChinese);
    }
    
    if ($group->strGroupId) 
    {
        _EchoTransactionParagraph($group, $bChinese);
	}
    
    EchoPromotionHead('gradedfund', $bChinese);
    if (AcctIsAdmin())
    {
        _echoAdminTestParagraph($group, $bChinese);
    }
}

function GradedFundEchoTitle($bChinese)
{
    global $group;
    
    $str = _GetStockDisplay($group->ref->stock_ref);
    if ($bChinese)  $str .= '分析工具';
    else              $str .= ' Analysis Tool';
    echo $str;
}

function GradedFundEchoMetaDescription($bChinese)
{
    global $group;
    
    $str = _GetStockDisplay($group->ref->stock_ref);
    if ($bChinese)  $str .= '和它相关的母基金以及分级B的净值分析计算网页工具. 分级基金是个奇葩设计, 简直就是故意给出套利机会, 让大家来交易增加流动性.';
    else              $str .= ' and its related funds net value calculation and analysis.';
    EchoMetaDescriptionText($str);
}

    AcctNoAuth();
    $group = new _GradedFundGroup(StockGetSymbolByUrl());
    
?>

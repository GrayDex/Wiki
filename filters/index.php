<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Wiki");
global $options;
// фильтры
global $arrFilter;
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$queryArr = $request->getQueryList()->getValues();

if (\Bitrix\Main\Loader::includeModule('iblock')) {
	$rsData = CIBlockProperty::GetList(
		arFilter: ['IBLOCK_CODE' => 'catalog']
	);

	$propInfoList = [];
	while ($arData = $rsData->GetNext()) {
		$propInfoList[$arData['CODE']] = $arData;
	}

	// заберем из $queryArr только фильтры
	$filterParams = array_intersect_key($queryArr, $propInfoList);

	foreach ($filterParams as $key => $value) {
		$propCode = $propInfoList[$key]['CODE'];
		$propPostfix = match ($propInfoList[$key]['PROPERTY_TYPE']) {
			'E' => '.CODE',
			'L' => '_VALUE',
			default => '',
		};
		$arrFilter['PROPERTY_' . $propCode . $propPostfix] = $value;
	}
}

// для применения фильтра в компоненте: "FILTER_NAME" => "arrFilter"
$APPLICATION->IncludeComponent(
	"bitrix:news.list", 
	"wiki_filters", 
	array(
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "N",
		"DISPLAY_PREVIEW_TEXT" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"FIELD_CODE" => array(
			0 => "SHOW_COUNTER",
			1 => "",
		),
		"FILTER_NAME" => "arrFilter",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => "28",
		"IBLOCK_TYPE" => "Wiki",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"INCLUDE_SUBSECTIONS" => "Y",
		"MESSAGE_404" => "",
		"NEWS_COUNT" => "20",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Новости",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => $options['CUR_DIR'][1],
		"PREVIEW_TRUNCATE_LEN" => "",
		"PROPERTY_CODE" => array(
			0 => "CATEGORY",
			1 => "",
		),
		"SET_BROWSER_TITLE" => "Y",
		"SET_LAST_MODIFIED" => "N",
		"SET_META_DESCRIPTION" => "Y",
		"SET_META_KEYWORDS" => "Y",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "Y",
		"SHOW_404" => "N",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "DESC",
		"SORT_ORDER2" => "ASC",
		"STRICT_SECTION_CHECK" => "N",
		"COMPONENT_TEMPLATE" => "wiki_filters"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
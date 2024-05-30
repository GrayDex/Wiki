<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die;

$filterUI = CIBlockSection::GetList(
	arFilter: ['IBLOCK_ID' => 28, 'CODE' => 'parent'],
	arSelect: ['UF_FILTERS_JSON']
)->GetNext()['UF_FILTERS_JSON'];

$res = json_decode($filterUI, true);

dd($res, dump: 1);

// make UI filters
if (!$_POST['ajaxPage']) {

	$filterUI = [];

	// set up list of filter keys from section field 'UF_FILTER_TUBE'
	if ($arParams['PARENT_SECTION_CODE']) {

		$filterUI = CIBlockSection::GetList(
			arFilter: ['IBLOCK_ID' => 5, 'CODE' => $arParams['PARENT_SECTION_CODE']],
			arSelect: ['UF_FILTERS']
		)->GetNext()['UF_FILTERS'];
	}

	// use default if empty
	if (!$filterUI) {
		$rsData = CIBlockElement::GetList(
			arOrder: ['ID' => 'DESC', 'GLOBAL_ACTIVE' => 'Y'],
			arFilter: ['IBLOCK_ID' => 9],
			arSelectFields: ['ID', 'IBLOCK_ID', 'PROPERTY_COMMON_FILTER']
		);
		while ($arData = $rsData->GetNext()) {
			$filterUI[] = $arData['PROPERTY_COMMON_FILTER_VALUE'];
		}
	}

	// get filter props
	if ($filterUI) {
		$filterProps = [];

		// get props info
		$rsData = CIBlockProperty::GetList(
			arFilter: ['IBLOCK_CODE' => 'catalog']
		);
		while ($arData = $rsData->GetNext()) {
			$filterProps[$arData['CODE']] = $arData;
		}

		// remove props that not exist into filterUI
		$filterProps = array_intersect_key($filterProps, array_flip($filterUI));

		// get values
		foreach ($filterUI as $code) {
			if ($filterProps[$code]) {

				$filterProps[$code]['VALUES'] = [];
				$rsData = CIBlockElement::GetList(
					arFilter: [
						'IBLOCK_ID' => $arParams['IBLOCK_ID'],
						'ACTIVE' => 'Y',
						'SECTION_ID' => $arParams['PARENT_SECTION'],
						'INCLUDE_SUBSECTIONS' => 'Y'
					],
					arGroupBy: ['PROPERTY_' . strtoupper($code)]
				);

				$data = [];
				$propKey = 'PROPERTY_' . strtoupper($code) . '_VALUE';
				while ($arData = $rsData->GetNext()) {

					if ($arData[$propKey]) {
						$data[] = $arData[$propKey];
					}
				}
				$filterProps[$code]['VALUES'] = array_unique($data);

				if ($filterProps[$code]['VALUES']) {
					natsort($filterProps[$code]['VALUES']);
				}

				if ($filterProps[$code]['PROPERTY_TYPE'] === 'E' && $filterProps[$code]['LINK_IBLOCK_ID']) {
					$filterProps[$code]['VALUES_LINK'] = [];
					$rsData = CIBlockElement::GetList(
						arFilter: [
							'IBLOCK_ID' => $filterProps[$code]['LINK_IBLOCK_ID'],
							'ACTIVE' => 'Y',
							'=ID' => $filterProps[$code]['VALUES']
						]
					);
					while ($arData = $rsData->GetNext()) {
						$filterProps[$code]['VALUES_LINK'][] = $arData;
					}
				}
			}
		}
		$arResult['PROPS']['FILTER_UI'] = array_chunk($filterProps, 6, preserve_keys: true);
		$arResult['PROPS']['FILTER_UI_MOB'] = $filterProps;
	}
}
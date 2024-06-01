<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die;
/** @var $arParams */
/** @var $arResult */

// фильтры
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if (!($request->isAjaxRequest() && $request->isPost())) {

	$filterUI = [];

	// список фильтров из свойства секции
	if ($arParams['PARENT_SECTION']) {

		$filterUIJSON = '';
		$sectionID = $arParams['PARENT_SECTION'];
		while (empty($filterUIJSON)) {

			$section = CIBlockSection::GetList(
				arFilter: ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $sectionID],
				arSelect: ['UF_FILTERS_JSON']
			)->GetNext();

			$filterUI = json_decode($section['~UF_FILTERS_JSON'], true);

			// если валидный массив то выход
			if ($filterUI) break;

			// если нет родителя то выход
			if (!$section['IBLOCK_SECTION_ID']) break;

			$sectionID = $section['IBLOCK_SECTION_ID'];
		}
	}

	// фильтры из ИБ настроек если не было в секциях
	if (!$filterUI) {

		$arData = CIBlockElement::GetList(
			arOrder: ['ID' => 'DESC'],
			arFilter: ['IBLOCK_ID' => 9],
			arNavStartParams: ['nTopCount' => 1, 'nPageSize' => 1],
			arSelectFields: ['PROPERTY_COMMON_FILTERS']
		)->GetNext();

		$filterUI = json_decode($arData['~PROPERTY_COMMON_FILTERS_VALUE'], true);
	}

	// если список фильтров есть
	if ($filterUI) {

		$filterProps = [];

		// получение всех свойств элементов ИБ
		$rsData = CIBlockProperty::GetList(
			arFilter: ['IBLOCK_ID' => $arParams['IBLOCK_ID']]
		);
		while ($arData = $rsData->GetNext()) {

			$filterProps[$arData['CODE']] = $arData;
		}

		// удаление ненужных сво-в
		$filterUI = array_intersect_key($filterUI, $filterProps);

		// список значений каждого сво-ва для фильтрации
		foreach ($filterUI as $code => $value) {

			$filterProps[$code]['INPUT_TYPE'] = $value['INPUT_TYPE'];

			// список значений текущего сво-ва
			$filterProps[$code]['VALUES'] = [];
			$propKey = 'PROPERTY_' . strtoupper($code);

			$rsData = CIBlockElement::GetList(
				arFilter: [
					'IBLOCK_ID' => $arParams['IBLOCK_ID'],
					'ACTIVE' => 'Y',
					'SECTION_ID' => $arParams['PARENT_SECTION'],
					'INCLUDE_SUBSECTIONS' => 'Y'
				],
				arGroupBy: [$propKey]
			);

			$data = [];
			while ($arData = $rsData->GetNext()) {

				if ($arData[$propKey . '_VALUE']) {
					$data[] = $arData[$propKey . '_VALUE'];
				}
			}
			$data = array_unique($data);
			natsort($data);
			$filterProps[$code]['VALUES'] = $data;

			if ($filterProps[$code]['INPUT_TYPE'] === 'RANGE') {

				$filterProps[$code]['VALUES'] = [
					'MIN_VALUE' => $filterProps[$code]['VALUES'][0],
					'MAX_VALUE' => $filterProps[$code]['VALUES'][array_key_last($filterProps[$code]['VALUES'])],
				];
			}

			// активность параметров фильтра
			$queryArr = $request->getQuery($code);
			if ($queryArr){

				foreach ($filterProps[$code]['VALUES'] as $prop){

					$filterProps[$code]['VALUES_ACTIVE'][] = $prop == $queryArr;
				}
			}

			// если значение сво-ва - ID
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
	$arResult['FILTER_UI'] = $filterProps;
}
dd($arResult['FILTER_UI']);
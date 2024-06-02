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
			arFilter: ['IBLOCK_ID' => $arParams['IBLOCK_ID']],
		);
		while ($arData = $rsData->GetNext()) {

			$filterProps[$arData['CODE']] = [
				'ID' => $arData['ID'],
				'CODE' => $arData['CODE'],
				'NAME' => $arData['NAME'],
				'PROPERTY_TYPE' => $arData['PROPERTY_TYPE'],
				'LINK_IBLOCK_ID' => $arData['LINK_IBLOCK_ID']
			];
		}

		// удаление ненужных сво-в
		$filterUI = array_intersect_key($filterUI, $filterProps);
		$filterProps = array_intersect_key($filterProps, $filterUI);


		// получение всех значений
		$propKeys = [];
		foreach ($filterProps as $code => $value) {

			$propKeys[] = 'PROPERTY_' . strtoupper($code);
		}

		$rsData = CIBlockElement::GetList(
			arFilter: [
				'IBLOCK_ID' => $arParams['IBLOCK_ID'],
				'ACTIVE' => 'Y',
				'SECTION_ID' => $arParams['PARENT_SECTION'],
				'INCLUDE_SUBSECTIONS' => 'Y'
			],
			arGroupBy: $propKeys
		);

		while ($arData = $rsData->GetNext()) {

			foreach ($filterProps as $code => $value) {

				if ($arData['PROPERTY_' . $code . '_VALUE']) {

					$filterProps[$code]['VALUES'][] = $arData['PROPERTY_' . $code . '_VALUE'];
				}
			}
		}

		// сортировка и поиск активных фильтров
		foreach ($filterProps as $code => $value) {

			$filterProps[$code]['INPUT_TYPE'] = $filterUI[$code]['INPUT_TYPE'];

			if (!$value['VALUES'] || !is_array($value['VALUES'])) {
				continue;
			}
			$filterProps[$code]['VALUES'] = array_unique($value['VALUES']);
			natsort($filterProps[$code]['VALUES']);

			// для восстановления нумерации ключей после сортировки
			$filterProps[$code]['VALUES'] = array_values($filterProps[$code]['VALUES']);

			// выбранные пользователем параметры фильтра
			$query = $request->getQuery($code);
			$filterProps[$code]['IS_SELECTED'] = [];
			foreach ($filterProps[$code]['VALUES'] as $key => $prop) {

				if ($query) {
					// если параметры в виде массива
					if (is_array($query)) {

						$filterProps[$code]['IS_SELECTED'][$key] = in_array($prop, $query);
					} else {

						$filterProps[$code]['IS_SELECTED'][$key] = $prop == $query;
					}
				}
			}

			// если значение сво-ва - ID элемента другого ИБ
			if ($filterProps[$code]['PROPERTY_TYPE'] === 'E' && $filterProps[$code]['LINK_IBLOCK_ID']) {

				$filterProps[$code]['VALUES_LINK'] = [];
				$rsData = CIBlockElement::GetList(
					arFilter: [
						'IBLOCK_ID' => $filterProps[$code]['LINK_IBLOCK_ID'],
						'ACTIVE' => 'Y',
						'=ID' => $filterProps[$code]['VALUES']
					],
					arSelectFields: ['IBLOCK_ID', 'ID', 'CODE', 'NAME']
				);

				while ($arData = $rsData->GetNext()) {

					$filterProps[$code]['VALUES_LINK'][] = $arData;
				}
			}
		}
	}
}
$arResult['FILTER_UI'] = $filterProps ?: [];
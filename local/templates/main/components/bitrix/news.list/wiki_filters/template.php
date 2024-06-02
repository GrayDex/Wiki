<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
/** @var $arResult */
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

?>
<br><br><br><br><br><br>

<section>
	<form js-news__form-filters>

		<?php foreach ($arResult['FILTER_UI'] as $filter): ?>

			<h2><?= $filter['NAME'] ?></h2>

			<?php if ($filter['INPUT_TYPE'] === 'RADIO'): ?>
				<?php foreach ($filter['VALUES'] as $key => $filterValue): ?>

					<input type="radio" id="<?= $filterValue ?>" name="<?= $filter['CODE'] ?>" value="<?= $filterValue ?>">
					<label for="<?= $filterValue ?>">
						<?= $filter['PROPERTY_TYPE'] !== 'E' ? $filterValue : $filter['VALUES_LINK'][$key]['NAME'] ?>
					</label>
					<br>

				<?php endforeach; ?>
			<?php endif ?>

			<?php if ($filter['INPUT_TYPE'] === 'SELECT'): ?>
				<label>
					<select name="<?= $filter['CODE'] ?>">
						<?php foreach ($filter['VALUES'] as $key => $filterValue): ?>

							<option value="<?= $filterValue ?>">
								<?= $filter['PROPERTY_TYPE'] !== 'E' ? $filterValue : $filter['VALUES_LINK'][$key]['NAME'] ?>
							</option>

						<?php endforeach; ?>
					</select>
				</label>
			<?php endif ?>

			<?php if ($filter['INPUT_TYPE'] === 'RANGE'): ?>

				<label for="from">От:</label>
				<input type="text" id="from" name="<?= $filter['CODE'] ?>[]" value="<?= $filter['VALUES']['MIN_VALUE'] ?>">
				<br><br>

				<label for="to">До:</label>
				<input type="text" id="to" name="<?= $filter['CODE'] ?>[]" value="<?= $filter['VALUES']['MAX_VALUE'] ?>">

			<?php endif ?>

			<br><br><br>
		<?php endforeach; ?>

		<br><br>
		<input type="submit" value="Применить">
	</form>
</section>

<br>
<hr><br><br>

<!-- список новостей -->
<section>
	<h2>Список новостей</h2>
	<br><br>

	<?php foreach ($arResult["ITEMS"] as $item): ?>
		<?php
		$this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
		?>
		<div id="<?= $this->GetEditAreaId($item['ID']); ?>">
			<h3>Название: <?= $item['NAME'] ?></h3>
			<p>Категория: <?= $item['PROPERTIES']['CATEGORY']['VALUE'] ?></p>
			<p>Рейтинг: <?= $item['PROPERTIES']['RATING']['VALUE'] ?></p>
			<p>Автор: <?= $item['PROPERTIES']['AUTHOR']['VALUE'] ?></p>
		</div>
		<br><br>
	<?php endforeach; ?>
</section>

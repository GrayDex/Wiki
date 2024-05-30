<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
?>
<br><br><br><br><br><br>

<section>
	<form action="/filters" method="post">
		<h2>Категория</h2>

		<input type="radio" id="politics" name="CATEGORY" value="Politics">
		<label for="politics">Политика</label><br>

		<input type="radio" id="sports" name="CATEGORY" value="Sports">
		<label for="sports">Спорт</label><br>

		<input type="radio" id="sports" name="CATEGORY" value="Science">
		<label for="sports">Наука</label><br>
		<br><br><br>

		<h2>Рейтинг</h2>
		<label for="from">От:</label>
		<input type="text" id="from" name="RATING" value="">
		<br><br>

		<label for="to">До:</label>
		<input type="text" id="to" name="RATING" value="">
		<br><br><br>

		<h2>Автор</h2>
		<select id="sortBy" name="sortBy">
			<option name="AUTHOR" value="">Сначала новые</option>
			<option value="AUTHOR">Сначала старые</option>
		</select>
		<br><br>
		<input type="submit" value="Применить">
	</form>
</section>

<br>
<hr><br><br>
<!-- список новостей -->
<section>
	<h2>Список новостей</h2>
	<?php foreach ($arResult["ITEMS"] as $item): ?>
		<?php
		$this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
		?>
		<div class="news-$item" data-category="politics" data-date="2022-01-01" id="<?= $this->GetEditAreaId($item['ID']); ?>">
			<h3>Название: <?= $item['NAME'] ?></h3>
			<p>Категория: <?= $item['PROPERTIES']['CATEGORY']['VALUE'] ?></p>
			<p>Рейтинг: <?= $item['PROPERTIES']['RATING']['VALUE'] ?></p>

			<p>Автор: <?= $item['PROPERTIES']['AUTHOR']['VALUE'] ?></p>
		</div>
		<br><br>
	<?php endforeach; ?>
</section>

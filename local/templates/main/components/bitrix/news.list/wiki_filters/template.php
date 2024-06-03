<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
/** @var $arResult */
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$queryArr = $request->GetQueryList()->GetValues();
?>
<br><br><br><br><br><br>

<?php if (!($request->isAjaxRequest() && $request->isPost())): ?>
    <section>
        <form><!-- js-news__form-filters -->

            <?php foreach ($arResult['FILTER_UI'] as $filter): ?>

                <h2><?= $filter['NAME'] ?></h2>

                <?php if ($filter['INPUT_TYPE'] === 'SELECT'): ?>
                    <label>
                        <select name="<?= $filter['CODE'] ?>">

                            <option value="">Все</option>

                            <?php foreach ($filter['VALUES'] as $key => $filterValue): ?>

                                <option value="<?= $filterValue ?>" <?= $filter['IS_SELECTED'][$key] ? 'selected' : '' ?>>
                                    <?= $filter['PROPERTY_TYPE'] !== 'E' ? $filterValue : $filter['VALUES_LINK'][$key]['NAME'] ?>
                                </option>

                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif ?>

                <?php if ($filter['INPUT_TYPE'] === 'RADIO'): ?>
                    <?php foreach ($filter['VALUES'] as $key => $filterValue): ?>

                        <input type="radio" id="<?= $filterValue ?>" name="<?= $filter['CODE'] ?>" value="<?= $filterValue ?>" <?= $filter['IS_SELECTED'][$key] ? 'checked' : '' ?>>
                        <label for="<?= $filterValue ?>">
                            <?= $filter['PROPERTY_TYPE'] !== 'E' ? $filterValue : $filter['VALUES_LINK'][$key]['NAME'] ?>
                        </label>
                        <br>

                    <?php endforeach; ?>
                <?php endif ?>

                <?php if ($filter['INPUT_TYPE'] === 'RANGE'): ?>

                    <label for="from">От:</label>
                    <?php $value = $queryArr[$filter['CODE'] . '_R'][0] ? max($queryArr[$filter['CODE'] . '_R'][0], $filter['VALUES']['MIN']) : $filter['VALUES']['MIN']; ?>
                    <input type="text" id="from" name="<?= $filter['CODE'] ?>_R[]" value="<?= $value ?>">
                    <br><br>

                    <label for="to">До:</label>
                    <?php $value = $queryArr[$filter['CODE'] . '_R'][1] ? min($queryArr[$filter['CODE'] . '_R'][1], $filter['VALUES']['MAX']) : $filter['VALUES']['MAX']; ?>
                    <input type="text" id="to" name="<?= $filter['CODE'] ?>_R[]" value="<?= $value ?>">

                <?php endif ?>

                <?php if ($filter['INPUT_TYPE'] === 'CHECKBOX'): ?>

                    <input type="checkbox" id="<?= $filter['CODE'] ?>" name="<?= $filter['CODE'] ?>"  >
                    <label for="<?= $filter['CODE'] ?>">
                        <?= $filter['NAME'] ?>
                    </label>

                <?php endif ?>

                <br><br><br>
            <?php endforeach; ?>

            <br><br>
            <input type="submit" value="Применить">
        </form>
    </section>
<?php endif ?>

<br>
<hr><br><br>

<!-- список новостей -->
<section>
    <h2 js-news__container>Список новостей</h2>
    <br><br>
    <?php if ($request->isAjaxRequest() && $request->isPost()) {
        $APPLICATION->RestartBuffer();
    } ?>

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
    <?php if ($request->isAjaxRequest() && $request->isPost()) die; ?>
</section>

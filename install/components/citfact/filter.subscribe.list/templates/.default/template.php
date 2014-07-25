<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$GLOBALS['APPLICATION']->AddHeadScript($templateFolder . '/script.js');
$GLOBALS['APPLICATION']->SetAdditionalCSS($templateFolder . '/style.css');
?>

<div class="row personal-groups">
    <div class="col-xs-12 personal-head">
        <h2><?= GetMessage('TITLE') ?></h2>
    </div>
</div>

<div class="row filter-subscribe-list">
    <? foreach ($arResult['ITEMS_CLEAR'] as $items): ?>
    <div class="col-xs-12 filter-one">
        <div class="col-xs-8">
            <ul>
                <? foreach ($items as $item): ?>
                    <li><span><?= $item['LABEL'] ?>:</span> <?= $item['VALUE'] ?></li>
                <? endforeach; ?>
            </ul>
        </div>
        <div class="col-xs-4 align-right">
            <form action="<?= POST_FORM_ACTION_URI ?>" data-remove-filter="true" method="post">
                <input type="hidden" name="COMPONENT_ID" value="<?= $arResult['COMPONENT_ID'] ?>">
                <input type="hidden" name="ACTION" value="DELETE">
                <input type="hidden" name="ID" value="<?= $item['ID'] ?>">
                <input type="submit" class="btn btn-gray big" value="<?= GetMessage('REMOVE_FILTER') ?>">
            </form>
        </div>
    </div>
    <? endforeach ?>
    <div class="col-xs-12 filter-one align-center<?= (empty($arResult['ITEMS_CLEAR'])) ? '' : ' hidden' ?>">
        <?= GetMessage('EMPTY_FILTER') ?>
    </div>
</div>

<script type="text/javascript">
    var FilterSubscribeUser = new FilterSubscribeUser({
        target: '[data-remove-filter]',
        el: '.filter-one'
    });
</script>
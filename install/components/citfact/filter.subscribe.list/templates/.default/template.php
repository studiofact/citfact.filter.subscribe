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
            <a href="<?= $item['DELETE_LINK'] ?>" class="btn btn-gray big"><?= GetMessage('REMOVE_FILTER') ?></a>
        </div>
    </div>
    <? endforeach ?>
</div>
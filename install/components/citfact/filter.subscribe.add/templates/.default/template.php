<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$GLOBALS['APPLICATION']->AddHeadScript($templateFolder . '/script.js');
$GLOBALS['APPLICATION']->SetAdditionalCSS($templateFolder . '/style.css');
?>


<div class="row filter-subscribe" style="display: none;" data-container="true">
    <form action="<?= POST_FORM_ACTION_URI ?>" id="filter-subscribe" method="post" enctype="multipart/form-data">

        <div class="col-xs-12 subscribe-info" data-template="subscribe-info">
            <div class="col-xs-6">
                <?= GetMessage('DESCRIPTION'); ?>
            </div>
            <div class="col-xs-6 align-right">
                <input type="submit" class="btn btn-green big" name="SAVE_FILTER" value="<?= GetMessage('SAVE'); ?>">
                <input type="submit" class="btn btn-green big" name="CANCEL_FILTER" value="<?= GetMessage('SAVE_CANCEL'); ?>">
            </div>
        </div>


        <div class="col-xs-12" data-template="subscribe-success">
            <div class="col-xs-7">
                <?= GetMessage('DESCRIPTION_SUCCES'); ?>
            </div>
            <div class="col-xs-5 align-center">
                <a class="btn btn-green big" href="/personal/notify/" target="_blank"><?= GetMessage('TO_FILTER'); ?></a>
            </div>
        </div>

    </form>
</div>

<script type="text/javascript">
    var FilterSubscribe = new FilterSubscribe({
        targetFilterForm: '#filtersmart',
        filterSubscribeForm: '#filter-subscribe'
    });
</script>
<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$GLOBALS['APPLICATION']->AddHeadScript($templateFolder . '/script.js');
$GLOBALS['APPLICATION']->SetAdditionalCSS($templateFolder . '/style.css');
?>


<div class="row filter-subscribe">
    <form action="<?= POST_FORM_ACTION_URI ?>" id="filter-subscribe" method="post" enctype="multipart/form-data">

        <div class="col-xs-12 subscribe-info">
            <div class="col-xs-6">
                Сохраните данный фильтр для получения уведомлений о новых преподавателях, интересующих Вас.
            </div>
            <div class="col-xs-6 align-right">
                <input type="submit" class="btn btn-green big" name="SAVE_FILTER" value="Сохранить">
                <input type="submit" class="btn btn-green big" name="CANCEL_FILTER" value="Нет, спасибо">
            </div>
        </div>

        <?/*
        <div class="col-xs-12">
            <div class="col-xs-7">
                Управление сохраненными фильтрами будет доступно в Вашем личном кабинете, на закладке "Уведомления"
            </div>
            <div class="col-xs-5 align-center">
                <a class="btn btn-green big" href="#">Перейти к управлению фильтрами</a>
            </div>
        </div>
        */?>
    </form>
</div>

<script type="text/javascript">
    var FilterSubscribe = new FilterSubscribe({
        targetFilterForm: '#filtersmart',
        filterSubscribeForm: '#filter-subscribe'
    });
</script>
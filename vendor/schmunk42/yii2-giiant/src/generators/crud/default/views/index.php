<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass;
$model->setScenario('crud');
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    /** @var \yii\db\ActiveRecord $model */
    $model = new $generator->modelClass;
    $safeAttributes = $model->safeAttributes();
    if (empty($safeAttributes)) {
        $safeAttributes = $model->getTableSchema()->columnNames;
    }
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\helpers\Url;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;

/**
* @var yii\web\View $this
* @var yii\data\ActiveDataProvider $dataProvider
* @var <?= ltrim($generator->searchModelClass, '\\') ?> $searchModel
*/

$this->title = <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="clearfix">
    <p class="pull-left">
        <?= "<?= " ?>Html::a('<span class="fa fa-plus"></span> ' . <?= $generator->generateString('Tambah Baru') ?>, ['create'], ['class' => 'btn btn-success']) ?>
    </p>
</div>


<?php if ($generator->indexWidgetType === 'grid'): ?>
<?= "<?php \yii\widgets\Pjax::begin(['id'=>'pjax-main', 'enableReplaceState'=> false, 'linkSelector'=>'#pjax-main ul.pagination a, th a', 'clientOptions' => ['pjax:success'=>'function(){alert(\"yo\")}']]) ?>\n"; ?>

<div class="box box-info">
    <div class="box-body">
        <div class="table-responsive">
            <?= "<?= " ?>GridView::widget([
            'layout' => '{summary}{pager}{items}{pager}',
            'dataProvider' => $dataProvider,
            'pager'        => [
                'class'          => yii\widgets\LinkPager::className(),
                'firstPageLabel' => <?= $generator->generateString('First') ?>,
                'lastPageLabel'  => <?= $generator->generateString('Last') ?>
            ],
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
            'headerRowOptions' => ['class'=>'x'],
            'columns' => [

            <?php
            $actionButtonColumn = <<<PHP
        [
            'class' => '{$generator->actionButtonClass}',
            'urlCreator' => function(\$action, \$model, \$key, \$index) {
                // using the column name as key, not mapping to 'id' like the standard generator
                \$params = is_array(\$key) ? \$key : [\$model->primaryKey()[0] => (string) \$key];
                \$params[0] = \Yii::\$app->controller->id ? \Yii::\$app->controller->id . '/' . \$action : \$action;
                return Url::toRoute(\$params);
            },
            'contentOptions' => ['nowrap'=>'nowrap']
        ],
PHP;

                // action buttons first
                    echo $actionButtonColumn;

                    $count = 0;
                    echo "\n"; // code-formatting

                    foreach ($safeAttributes as $attribute) {
                        $format = trim($generator->columnFormat($attribute,$model));
                        if ($format == false) continue;
                        if (++$count < $generator->gridMaxColumns) {
                            echo "\t\t\t{$format},\n";
                        } else {
                            echo "\t\t\t/*{$format}*/\n";
                        }
                    }

                    ?>
                    ],
                ]); ?>
        </div>
    </div>
</div>

<?= "<?php \yii\widgets\Pjax::end() ?>\n"; ?>

<?php else: ?>

    <?= "<?= " ?> ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'item'],
    'itemView' => function ($model, $key, $index, $widget) {
    return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
    },
    ]); ?>

<?php endif; ?>

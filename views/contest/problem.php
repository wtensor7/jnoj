<?php

use app\models\Solution;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Nav;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $solution app\models\Solution */
/* @var $problem array */
/* @var $submissions array */

$this->title = Html::encode($model->title) . ' - ' . $problem['title'];
$this->params['model'] = $model;

if (!Yii::$app->user->isGuest) {
    $solution->language = Yii::$app->user->identity->language;
}
$problems = $model->problems;
if (empty($problems)) {
    echo 'Please add question';
    return;
}

$nav = [];
foreach ($problems as $key => $p) {
    $nav[] = [
        'label' => chr(65 + $key),
        'url' => [
            'problem',
            'id' => $model->id,
            'pid' => $key,
        ]
    ];
}
$sample_input = unserialize($problem['sample_input']);
$sample_output = unserialize($problem['sample_output']);
?>
<div class="problem-view">
    <div class="text-center">
        <?= Nav::widget([
            'items' => $nav,
            'options' => ['class' => 'pagination']
        ]) ?>
    </div>
    <div class="row">
        <div class="col-md-8 problem-view">
            <h1><?= Html::encode(chr(65 + $problem['num']) . '. ' . $problem['title']) ?></h1>

            <h3><?= Yii::t('app', 'Description') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asHtml($problem['description']) ?>
            </div>

            <h3><?= Yii::t('app', 'Input') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asHtml($problem['input']) ?>
            </div>

            <h3><?= Yii::t('app', 'Output') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asHtml($problem['output']) ?>
            </div>

            <h3><?= Yii::t('app', 'Examples') ?></h3>
            <div class="content-wrapper">
                <div class="sample-test">
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= $sample_input[0] ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= $sample_output[0] ?></pre>
                    </div>

                    <?php if (!empty($sample_input[1])):?>
                        <div class="input">
                            <h4><?= Yii::t('app', 'Input') ?></h4>
                            <pre><?= $sample_input[1] ?></pre>
                        </div>
                        <div class="output">
                            <h4><?= Yii::t('app', 'Output') ?></h4>
                            <pre><?= $sample_output[1] ?></pre>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($sample_input[2])):?>
                        <div class="input">
                            <h4><?= Yii::t('app', 'Input') ?></h4>
                            <pre><?= $sample_input[2] ?></pre>
                        </div>
                        <div class="output">
                            <h4><?= Yii::t('app', 'Output') ?></h4>
                            <pre><?= $sample_output[2] ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($problem['hint'])): ?>
                <h3><?= Yii::t('app', 'Hint') ?></h3>
                <div class="content-wrapper">
                    <?= Yii::$app->formatter->asHtml($problem['hint']) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4 problem-info">
            <div class="panel panel-default">
                <!-- Table -->
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Time limit</td>
                        <td>
                            <?= Yii::t('app', '{t, plural, =1{# second} other{# seconds}}', ['t' => intval($problem['time_limit'])]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Memory limit</td>
                        <td><?= $problem['memory_limit'] ?> MB</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#submit-solution"><span class="glyphicon glyphicon-plus"></span> Submit a solution</button>

            <?php if (!Yii::$app->user->isGuest && !empty($submissions)): ?>
            <div class="panel panel-default" style="margin-top: 40px">
                <div class="panel-heading">Submissions</div>
                <!-- Table -->
                <table class="table">
                    <tbody>
                    <?php foreach($submissions as $sub): ?>
                    <tr>
                        <td><?= $sub['created_at'] ?></td>
                        <td>
                            <?php
                                if ($sub['result'] == Solution::OJ_AC) {
                                    $span = '<span class="label label-success">' . Solution::getResultList($sub['result']) . '</span>';
                                    echo Html::a($span,
                                        ['/solution/source', 'id' => $sub['id']],
                                        ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                    );
                                } else if ($sub['result'] == Solution::OJ_CE) {
                                    $span = '<span class="label label-default">' . Solution::getResultList($sub['result']) . '</span>';
                                    echo Html::a($span,
                                        ['/solution/result', 'id' => $sub['id']],
                                        ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                    );
                                } else {
                                    $span = '<span class="label label-default">' . Solution::getResultList($sub['result']) . '</span>';
                                    echo Html::a($span,
                                        ['/solution/source', 'id' => $sub['id']],
                                        ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                    );
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
<div id="solution-content">
</div>
<?php Modal::end(); ?>

<?php Modal::begin([
    'header' => '<h3>'.Yii::t('app','Submit a solution').'</h3>',
    'size' => Modal::SIZE_LARGE,
    'options' => ['id' => 'submit-solution']
]); ?>
<?php if ($model->getRunStatus() == app\models\Contest::STATUS_ENDED): ?>
    <?= Yii::t('app', 'The contest has ended.') ?>
<?php else: ?>

    <?php if (Yii::$app->user->isGuest): ?>
        <?= app\widgets\login\Login::widget(); ?>
    <?php else: ?>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($solution, 'language')->dropDownList($solution::getLanguageList()) ?>

        <?= $form->field($solution, 'source')->widget('app\widgets\codemirror\CodeMirror'); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    <?php endif; ?>

<?php endif; ?>

<?php Modal::end(); ?>
<?php
$this->registerJs('
$(document).ready(function () {
    $(".katex.math.inline").each(function () {
        var parent = $(this).parent()[0];
        if (parent.localName !== "code") {
            var texTxt = $(this).text();
            var el = $(this).get(0);
            try {
                katex.render(texTxt, el);
            } catch (err) {
                $(this).html("<span class=\'err\'>" + err);
            }
        } else {
            $(this).parent().text($(this).parent().text());
        }
    });
    $(".katex.math.multi-line").each(function () {
        var texTxt = $(this).text();
        var el = $(this).get(0);
        try {
            katex.render(texTxt, el, {displayMode: true})
        } catch (err) {
            $(this).html("<span class=\'err\'>" + err)
        }
    });
    $(".pre p").each(function(i, block) {  // use <pre><p>
        hljs.highlightBlock(block);
    });
})
');
?>

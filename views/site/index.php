<?php
/* @var $this yii\web\View */
/* @var $model app\models\User */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Welcome!</h1>
    </div>

    <div class="body-content">
        <?php if (Yii::$app->user->isGuest) { ?>
            <p class="lead">Please <a href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>">login</a> to see more.</p>
        <?php } elseif (!empty($model)) { ?>
            <p>You can edit your email bellow.</p>
            <?php
            $form = ActiveForm::begin([
                    'id'          => 'login-form',
                    'options'     => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template'     => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
                        'labelOptions' => ['class' => 'col-lg-1 control-label'],
                    ],
            ]);
            ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <div class="form-group">
                <div class="col-lg-offset-1 col-lg-11">
                    <?= Html::submitButton('Edit', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        <?php } ?>
    </div>
</div>

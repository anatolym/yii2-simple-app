<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\User;
use yii\web\BadRequestHttpException;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = null;
        // If form was passed, handle it.
        if (!Yii::$app->user->isGuest) {
            $model           = Yii::$app->user->identity;
            $model->scenario = 'edit';
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('info', 'New email saved.');
                return $this->goHome();
            }
        }
        return $this->render('index', [
                'model' => $model,
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $token = Yii::$app->request->get('passwordResetToken');
        if (!is_null($token)) {
            $user = User::findByPasswordResetToken($token);
            if (User::isPasswordResetTokenValid($token) && !empty($user)) {
                $isNew        = $user->status === User::STATUS_UNCONFIRMED;
                $user->removePasswordResetToken();
                $user->status = User::STATUS_ACTIVE;
                $user->save(false);
                $user->login();
                $message      = $isNew ?
                    'Welcome, new user!' :
                    'Glad to see you again, old friend!';
                Yii::$app->session->setFlash('info', $message);
                return $this->goHome();
            } else {
                throw new BadRequestHttpException();
            }
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->recovery()) {
            Yii::$app->session->setFlash('info', 'Login url was sent to your email.');
            return $this->goHome();
        }
        return $this->render('login', [
                'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

}

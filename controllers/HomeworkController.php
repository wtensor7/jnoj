<?php

namespace app\controllers;

use app\models\Problem;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
use yii\filters\AccessControl;
use app\models\Homework;
use app\models\ContestAnnouncement;

/**
 * HomeworkController implements the CRUD actions for Homework model.
 */
class HomeworkController extends ContestController
{
    public $layout = 'homework';

    public function init()
    {
        Yii::$app->language = 'zh-CN';
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create'],
                'rules' => [
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Homework models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->layout = 'main';

        $query = Homework::find()->with('user')->where([
            'type' => Homework::TYPE_HOMEWORK,
            'status' => Homework::STATUS_PUBLISHED
        ])->orderBy(['id' => SORT_DESC]);

        if (!Yii::$app->user->isGuest) {
            $query->orWhere([
                'type' => Homework::TYPE_HOMEWORK,
                'created_by' => Yii::$app->user->id
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Homework model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Homework model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $this->layout = 'main';
        $model = new Homework();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 删除一个问题
     * @param $id
     * @param $pid
     * @return \yii\web\Response
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    public function actionDeleteproblem($id, $pid)
    {
        $model = $this->findModel($id);
        // 权限判断
        if ($model->type != Homework::TYPE_HOMEWORK || Yii::$app->user->isGuest || $model->created_by != Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        $ok = Yii::$app->db->createCommand()
            ->delete('{{%contest_problem}}', ['contest_id' => $id, 'problem_id' => $pid])
            ->execute();
        if ($ok) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Delete successfully'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Delete failed'));
        }
        return $this->redirect(['/homework/update', 'id' => $id]);
    }

    /**
     * 增加一个问题
     * @param $id
     * @return \yii\web\Response
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    public function actionAddproblem($id)
    {
        $model = $this->findModel($id);

        // 权限判断
        if ($model->type != Homework::TYPE_HOMEWORK || Yii::$app->user->isGuest || $model->created_by != Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        if (($post = Yii::$app->request->post())) {
            $pid = intval($post['problem_id']);
            $has_problem = (new Query())->select('id')
                ->from('{{%problem}}')
                ->where('id=:id AND status=:status', [':id' => $pid, ':status' => Problem::STATUS_VISIBLE])
                ->exists();
            if ($has_problem) {
                $problem_in_contest = (new Query())->select('problem_id')
                    ->from('{{%contest_problem}}')
                    ->where(['problem_id' => $pid, 'contest_id' => $model->id])
                    ->exists();
                if ($problem_in_contest) {
                    Yii::$app->session->setFlash('info', Yii::t('app', 'This problem has in the contest.'));
                    return $this->redirect(['/homework/update', 'id' => $id]);
                }
                $count = (new Query())->select('contest_id')
                    ->from('{{%contest_problem}}')
                    ->where(['contest_id' => $model->id])
                    ->count();

                Yii::$app->db->createCommand()->insert('{{%contest_problem}}', [
                    'problem_id' => $pid,
                    'contest_id' => $model->id,
                    'num' => $count
                ])->execute();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Submit successfully'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'No such problem.'));
            }
            return $this->redirect(['/homework/update', 'id' => $id]);
        }
    }

    /**
     * 修改一个问题
     * @param $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    public function actionUpdateproblem($id)
    {
        $model = $this->findModel($id);

        // 权限判断
        if ($model->type != Homework::TYPE_HOMEWORK || Yii::$app->user->isGuest || $model->created_by != Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        if (($post = Yii::$app->request->post())) {
            $pid = intval($post['problem_id']);
            $new_pid = intval($post['new_problem_id']);

            $has_problem1 = (new Query())->select('id')
                ->from('{{%problem}}')
                ->where('id=:id AND status=:status', [':id' => $pid, ':status' => Problem::STATUS_VISIBLE])
                ->exists();

            $has_problem2 = (new Query())->select('id')
                ->from('{{%problem}}')
                ->where('id=:id AND status=:status', [':id' => $new_pid, ':status' => Problem::STATUS_VISIBLE])
                ->exists();
            if ($has_problem1 && $has_problem2) {
                $problem_in_contest = (new Query())->select('problem_id')
                    ->from('{{%contest_problem}}')
                    ->where(['problem_id' => $new_pid, 'contest_id' => $model->id])
                    ->exists();
                if ($problem_in_contest) {
                    Yii::$app->session->setFlash('info', Yii::t('app', 'This problem has in the contest.'));
                    return $this->refresh();
                }

                Yii::$app->db->createCommand()->update('{{%contest_problem}}', [
                    'problem_id' => $new_pid,
                ], ['problem_id' => $pid, 'contest_id' => $model->id])->execute();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Submit successfully'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'No such problem.'));
            }
            return $this->redirect(['/homework/update', 'id' => $id]);
        }
    }

    /**
     * 榜单
     * @param integer $id
     * @return mixed
     */
    public function actionStanding($id)
    {
        $model = $this->findModel($id);
        return $this->render('standing', [
            'model' => $model
        ]);
    }

    /**
     * Updates an existing Homework model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    public function actionUpdate($id)
    {
        $this->layout = 'main';
        $model = $this->findModel($id);

        // 权限判断
        if ($model->type != Homework::TYPE_HOMEWORK || Yii::$app->user->isGuest || $model->created_by != Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->refresh();
        }

        $announcements = new ActiveDataProvider([
            'query' => ContestAnnouncement::find()->where(['contest_id' => $model->id])
        ]);

        $newAnnouncement = new ContestAnnouncement();
        if ($newAnnouncement->load(Yii::$app->request->post())) {
            $newAnnouncement->contest_id = $model->id;
            $newAnnouncement->save();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Save successfully'));
            return $this->refresh();
        }

        return $this->render('update', [
            'model' => $model,
            'announcements' => $announcements,
            'newAnnouncement' => $newAnnouncement
        ]);
    }

    /**
     * Deletes an existing Homework model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
}

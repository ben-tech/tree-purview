<?php

namespace bmte\treepurview;

use Yii;
use Closure;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\Column;

/**
 * ActionColumn is a column for the [[GridView]] widget that displays buttons for viewing and manipulating the items.
 *
 * To add an ActionColumn to the gridview, add it to the [[GridView::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => ActionColumn::className(),
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionColumn extends Column
{
    /**
     * @inheritdoc
     */
    public $headerOptions = ['class' => 'action-column'];
    /**
     * @var string the ID of the controller that should handle the actions specified here.
     * If not set, it will use the currently active controller. This property is mainly used by
     * [[urlCreator]] to create URLs for different actions. The value of this property will be prefixed
     * to each action name to form the route of the action.
     */
    public $controller;
    /**
     * @var string the template used for composing each cell in the action column.
     * Tokens enclosed within curly brackets are treated as controller action IDs (also called *button names*
     * in the context of action column). They will be replaced by the corresponding button rendering callbacks
     * specified in [[buttons]]. For example, the token `{view}` will be replaced by the result of
     * the callback `buttons['view']`. If a callback cannot be found, the token will be replaced with an empty string.
     *
     * As an example, to only have the view, and update button you can add the ActionColumn to your GridView columns as follows:
     *
     * ```php
     * ['class' => 'yii\grid\ActionColumn', 'template' => '{view} {update}'],
     * ```
     *
     * @see buttons
     */
    public $template = '{view} {update} {delete}';
    /**
     * @var array button rendering callbacks. The array keys are the button names (without curly brackets),
     * and the values are the corresponding button rendering callbacks. The callbacks should use the following
     * signature:
     *
     * ```php
     * function ($url, $model, $key) {
     *     // return the button HTML code
     * }
     * ```
     *
     * where `$url` is the URL that the column creates for the button, `$model` is the model object
     * being rendered for the current row, and `$key` is the key of the model in the data provider array.
     *
     * You can add further conditions to the button, for example only display it, when the model is
     * editable (here assuming you have a status field that indicates that):
     *
     * ```php
     * [
     *     'update' => function ($url, $model, $key) {
     *         return $model->status === 'editable' ? Html::a('Update', $url) : '';
     *     },
     * ],
     * ```
     */
    public $buttons = [];
    /** @var array visibility conditions for each button. The array keys are the button names (without curly brackets),
     * and the values are the boolean true/false or the anonymous function. When the button name is not specified in
     * this array it will be shown by default.
     * The callbacks must use the following signature:
     *
     * ```php
     * function ($model, $key, $index) {
     *     return $model->status === 'editable';
     * }
     * ```
     *
     * Or you can pass a boolean value:
     *
     * ```php
     * [
     *     'update' => \Yii::$app->user->can('update'),
     * ],
     * ```
     * @since 2.0.7
     */
    public $visibleButtons = [];
    /**
     * @var callable a callback that creates a button URL using the specified model information.
     * The signature of the callback should be the same as that of [[createUrl()]].
     * If this property is not set, button URLs will be created using [[createUrl()]].
     */
    public $urlCreator;
    /**
     * @var array html options to be applied to the [[initDefaultButtons()|default buttons]].
     * @since 2.0.4
     */
    public $buttonOptions = [];

    /**
     * @var 权限管理类
     */
    public $enumClass;

    /**
     * @var 权限数据
     */
    public $data;

    /**
     * Creates a URL for the given action and model.
     * This method is called for each button and each row.
     * @param string $action the button name (or action ID)
     * @param \yii\db\ActiveRecord $model the data model
     * @param mixed $key the key associated with the data model
     * @param integer $index the current row index
     * @return string the created URL
     */
    public function createUrl($action, $model, $key, $index)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index);
        } else {
            $params = is_array($key) ? $key : ['id' => (string) $key];
            $params[0] = $this->controller ? $this->controller . '/' . $action : $action;

            return Url::toRoute($params);
        }
    }

    public function init()
    {
        parent::init();
        if(isset($this->data) && $this->data!='')
        {
            $this->data=json_decode($this->data,true);
        }else
            $this->data=null;
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $dict = $this->enumClass->getDictionary();
        //合并菜单
        if($model->link_url=='#'||trim($model->link_url)=='')
            return '';

        $menu = $this->mergePowerMenu($dict,$model->rights_val,$key);

        $html = '';
        if(count($menu)>0)
        {
            foreach ($menu as $k=>$v)
            {
                if(is_array($v)&&count($v)>0)
                {
                    foreach($v as $vv)
                    {
                       $html.= Html::checkbox(sprintf('p[%s][%s]',$k,$vv['value']),$vv['checked']==1?true:false,['value'=>$vv['value'],'label'=>$vv['text']]).'&nbsp;&nbsp;';
                    }
                }
            }
        }
        return $html;

//        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
//            $name = $matches[1];
//
//            if (isset($this->visibleButtons[$name])) {
//                $isVisible = $this->visibleButtons[$name] instanceof \Closure
//                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
//                    : $this->visibleButtons[$name];
//            } else {
//                $isVisible = true;
//            }
//
//            if ($isVisible && isset($this->buttons[$name])) {
//                $url = $this->createUrl($name, $model, $key, $index);
//                return call_user_func($this->buttons[$name], $url, $model, $key);
//            } else {
//                return '';
//            }
//        }, $this->template);
    }

    /**
     * 合并菜单
     * @param $dict
     * @param $rights
     * @param $menu_id
     * @return array
     */
    protected function mergePowerMenu($dict,$rights,$menu_id)
    {
        $menu = [];
        foreach ($dict as $k=>$v)
        {
            if($rights & $k == $k)
            {
                $menu[$menu_id][]=[
                    'value'=>$k,
                    'text'=>$v,
                    'checked'=>$this->isChecked($menu_id,$k)
                ];
            }
        }
        return $menu;
    }

    /**
     * 判断是否选中菜单
     * @param $menu_id
     * @param $value
     */
    private function isChecked($menu_id,$value)
    {
        if(isset($this->data)
            && is_array($this->data)
            && isset($this->data[$menu_id]))
        {
            return intval($this->data[$menu_id]&$value)===intval($value);
        }
        return false;
    }

}

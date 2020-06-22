<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-15
 * Time: 20:59
 */

namespace common\seo;


use yii\helpers\Url;
use yii\web\View;

/**
 * Регестрируем мета теги на странице.
 * ```php
 *      //можно зарегистрировать определенный тип тегов
 *      MetaTag::init($view, $category)->registerOpenGraphTag();
 *
 *      //или  registerAll для регистрации OpenGraph and SEO тегов
 *      MetaTag::init($view, $category)->registerAll();
 *      // ...
 * ]
 * ```
 *
 * Class MetaTag
 * @package common\seo
 */
class MetaTag
{
    public $skipTags = [];
    protected $openGraphTags = [];

    protected $specialTags = [
        [
            'name' => 'mailru-verification',
            'content' => '7e1f1b5f8cbab80d',
            'home_only' => true
        ]
    ];

    /**
     * @var View
     */
    protected $view;
    /**
     * @var SeoFields
     */
    protected $model;


    /**
     * MetaTag constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $attribute => $value) {
            $this->{$attribute} = $value;
        }
    }

    /**
     * Инициализация класса
     * @param View $view
     * @param $model
     * @param array $openGraphTags
     * @param array $skipTags
     * @return MetaTag
     */
    public static function init(View $view, $model, array $openGraphTags = [], array $skipTags = []): MetaTag
    {
        $defaultOpenGraphTags = [
            'type' => 'website',
            'site_name' => 'shopandshow.ru',
            'locale' => 'ru_RU',
            'url' => Url::current(),
            'image' => \Yii::$app->urlManager->baseUrl . '/v2/common/img/og/open_graph_logo.png'
        ];

        $openGraphTags = empty($openGraphTags)
            ? $defaultOpenGraphTags : array_merge($defaultOpenGraphTags, $openGraphTags);

        return new static(compact(['view', 'model', 'openGraphTags', 'skipTags']));
    }

    /**
     * Регестрируем основыне мета теги
     * @return MetaTag
     */
    public function registerSeoTag(): MetaTag
    {
        if (!in_array('title', $this->skipTags)) {
            $this->view->title = $this->getTitle();
        }
        if (!in_array('description', $this->skipTags)) {
            $this->view->registerMetaTag([
                'name' => 'description',
                'content' => $this->getDescription(),
            ], 'description');
        }


        foreach ($this->specialTags as $tag) {

            if (!in_array($tag, $this->skipTags)) {
                if ($tag['home_only']) {
                    if (\Yii::$app->controller->route == 'site/index') {
                        $this->view->registerMetaTag([
                            'name' => $tag['name'],
                            'content' => $tag['content'],
                        ]);
                    }
                } else {
                    $this->view->registerMetaTag([
                        'name' => $tag['name'],
                        'content' => $tag['content'],
                    ]);
                }
            }

        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        if (method_exists($this->model, 'getSeoTitle')) {
            $title = $this->model->getSeoTitle();
        } else {
            $title = $this->model->meta_title ?? '';
            if (!$title && isset($this->model->name)) {
                $title = $this->model->name;
            }
        }

        return static::replacementChunks($title);
    }

    public static function replacementChunks($str, $replacements = [])
    {
        $pageNumber = (int)\Yii::$app->request->get('page');

        $replacementsDefault = [
            '{pageNumber} ' => empty($pageNumber) ? '' : " – страница №{$pageNumber}"
        ];
        $replacements = array_merge($replacementsDefault, $replacements);
        foreach ($replacements as $key => $value) {
            $str = str_replace($key, $value, $str);
        }
        return $str;
    }

    public function getDescription()
    {
        if (method_exists($this->model, 'getSeoDescription')) {
            $metaDescription = $this->model->getSeoDescription();
        } else {
            $metaDescription = $this->model->name ?? '';
            if (!$metaDescription && isset($this->model->name)) {
                $metaDescription = $this->model->name;
            }
        }
        return $metaDescription;
    }

    /**
     *  Регестрируем Open Graph теги
     */
    public function registerOpenGraphTag(): MetaTag
    {
        //добавляем title и description
        $this->populateOpenGraphTags();

        foreach ($this->openGraphTags as $tag => $content) {

            if ($content) {
                $metaTag = $this->normalizeOpenGraphTag($tag, $content);
                $this->view->registerMetaTag($metaTag);
            }
        }
        return $this;
    }

    /**
     * Регестируем все сео теги
     * @return MetaTag
     */
    public function registerAll(): MetaTag
    {
        return $this
            ->registerSeoTag()
            ->registerOpenGraphTag();
    }

    /** добавляет title и description */
    protected function populateOpenGraphTags()
    {
        if ($this->model && !in_array('title', $this->skipTags)) {
            $this->openGraphTags['title'] = static::replacementChunks($this->model->getSeoTitle());

        }
        if ($this->model && !in_array('description', $this->skipTags)) {
            $this->openGraphTags['description'] = $this->model->getOpenGraphDescription();
        }

    }

    /**
     * Нормализация тега для регистрации во вью
     * @param string $tag
     * @param string $content
     * @return array
     */
    protected function normalizeOpenGraphTag(string $tag, string $content = ''): array
    {
        return [
            'property' => "og:$tag",
            'content' => $content
        ];
    }

}

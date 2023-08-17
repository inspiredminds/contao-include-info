<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\Aggregator;

use Contao\ArticleModel;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\FormModel;
use Contao\FrontendTemplate;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\ThemeModel;
use InspiredMinds\IncludeInfoBundle\Model\InsertTagIndexModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

final class IncludesAggregator
{
    private $router;
    private $requestStack;
    private $tokenManager;
    private $tokenName;

    public function __construct(RouterInterface $router, RequestStack $requestStack, ContaoCsrfTokenManager $tokenManager, string $tokenName)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->tokenManager = $tokenManager;
        $this->tokenName = $tokenName;
    }

    public function renderIncludesForArticle(ArticleModel $article): ?string
    {
        $includes = $this->getArticleIncludes((int) $article->id);

        return $this->renderIncludes(null, $includes, ArticleModel::getTable());
    }

    public function renderIncludesForForm(FormModel $form): ?string
    {
        $includes = array_merge($this->getFormIncludes((int) $form->id), $this->getInsertTags('insert_form', (int) $form->id));

        return $this->renderIncludes(null, $includes, FormModel::getTable());
    }

    public function renderIncludesForModule(ModuleModel $module): ?string
    {
        $includes = array_merge($this->getModuleIncludes((int) $module->id), $this->getInsertTags('insert_module', (int) $module->id));

        return $this->renderIncludes(null, $includes, ModuleModel::getTable());
    }

    public function renderIncludesForContentElement(ContentModel $element): ?string
    {
        // Get the type
        $type = $element->type;

        // Prepare data
        $original = null;
        $includes = null;

        // Determine aggregation
        if ('alias' === $type) {
            $original = $this->getContentElement((int) $element->cteAlias);
            $includes = $this->getContentElementIncludes((int) $element->cteAlias, (int) $element->id);
        } elseif ('article' === $type) {
            $original = $this->getArticle((int) $element->articleAlias);
            $includes = $this->getArticleIncludes((int) $element->articleAlias, (int) $element->id);
        } elseif ('form' === $type) {
            $original = $this->getForm((int) $element->form);
            $includes = $this->getFormIncludes((int) $element->form, (int) $element->id);
        } elseif ('module' === $type) {
            $original = $this->getModule((int) $element->module);
            $includes = $this->getModuleIncludes((int) $element->module, (int) $element->id);
        } else {
            $includes = $this->getContentElementIncludes((int) $element->id);
        }

        // Render includes
        return $this->renderIncludes($original, $includes, ContentModel::getTable());
    }

    private function getContentElementIncludes(int $elementId, int $ignoreId = null): array
    {
        return array_merge($this->getContentElements('cteAlias', 'alias', $elementId, $ignoreId), $this->getInsertTags('insert_content', $elementId));
    }

    private function getContentElement(int $contentElementId): ?array
    {
        // Get the content element
        $element = ContentModel::findByPk($contentElementId);

        if (null === $element || $element->ptable !== ArticleModel::getTable()) {
            return null;
        }

        // Get the parent
        $article = ArticleModel::findByPk($element->pid);

        if (null === $article) {
            return null;
        }

        // Get the parent pages
        $pages = PageModel::findParentsById($article->pid);

        if (null === $pages) {
            return null;
        }

        // Get the page titles
        $pageTitles = array_reverse($pages->fetchEach('title'));

        // create breadcrumb
        return [
            'crumbs' => $pageTitles,
            'title' => $article->title,
            'link' => $this->router->generate('contao_backend', [
                'do' => 'article',
                'table' => 'tl_content',
                'id' => $article->id,
                'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
            ]),
        ];
    }

    private function getArticleIncludes(int $articleId, int $ignoreId = null): array
    {
        return array_merge($this->getContentElements('articleAlias', 'article', $articleId, $ignoreId), $this->getInsertTags('insert_article', $articleId));
    }

    private function getArticle(int $articleId): ?array
    {
        // Get the article
        $article = ArticleModel::findByPk($articleId);

        if (null === $article) {
            return null;
        }

        // Get the parent pages
        $pages = PageModel::findParentsById($article->pid);

        if (null === $pages) {
            return null;
        }

        // Get the page titles
        $pageTitles = array_reverse($pages->fetchEach('title'));

        return [
            'crumbs' => $pageTitles,
            'title' => $article->title,
            'link' => $this->router->generate('contao_backend', [
                'do' => 'article',
                'table' => 'tl_content',
                'id' => $article->id,
                'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
            ]),
        ];
    }

    private function getForm(int $formId): ?array
    {
        // Get the form
        $form = FormModel::findByPk($formId);

        if (null === $form) {
            return null;
        }

        return [
            'crumbs' => [],
            'title' => $form->title,
            'link' => $this->router->generate('contao_backend', [
                'do' => 'form',
                'table' => 'tl_form_field',
                'id' => $form->id,
                'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
            ]),
        ];
    }

    private function getModule(int $moduleId): ?array
    {
        // Get the module
        $module = ModuleModel::findByPk($moduleId);

        if (null === $module) {
            return null;
        }

        // Get the theme
        $theme = ThemeModel::findByPk((int) $module->pid);

        if (null === $theme) {
            return null;
        }

        return [
            'crumbs' => [$theme->name],
            'title' => $module->name,
            'link' => $this->router->generate('contao_backend', [
                'do' => 'themes',
                'table' => 'tl_module',
                'id' => $module->id,
                'act' => 'edit',
                'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
                'rt' => $this->tokenManager->getToken($this->tokenName),
            ]),
        ];
    }

    private function getFormIncludes(int $formId, int $ignoreId = null): array
    {
        return $this->getContentElements('form', 'form', $formId, $ignoreId);
    }

    private function getModuleIncludes(int $moduleId, int $ignoreId = null): array
    {
        $includes = $this->getContentElements('module', 'module', $moduleId, $ignoreId);

        if (null === ($module = ModuleModel::findByPk($moduleId))) {
            return $includes;
        }

        $theme = ThemeModel::findByPk((int) $module->pid);

        if (null !== ($layouts = LayoutModel::findByPid($theme->id))) {
            foreach ($layouts as $layout) {
                $modules = StringUtil::deserialize($layout->modules, true);

                foreach ($modules as $layoutModule) {
                    if ($moduleId === (int) $layoutModule['mod']) {
                        $includes[] = [
                            'crumbs' => [$theme->name],
                            'title' => $layout->name,
                            'link' => $this->router->generate('contao_backend', [
                                'do' => 'themes',
                                'table' => 'tl_layout',
                                'id' => $layout->id,
                                'act' => 'edit',
                                'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
                                'rt' => $this->tokenManager->getToken($this->tokenName),
                            ]),
                        ];
                        break;
                    }
                }
            }
        }

        return $includes;
    }

    private function getContentElements(string $idField, string $elementType, int $id, int $ignoreId = null): array
    {
        // Prepare array
        $includes = [];

        // Query
        $t = ContentModel::getTable();
        $columns = ["$t.$idField = ?", "$t.type = ?"];
        $values = [$id, $elementType];

        if (null !== $ignoreId) {
            $columns[] = "$t.id != ?";
            $values[] = $ignoreId;
        }

        // Get all elements where this element is included
        if (null !== ($elements = ContentModel::findBy($columns, $values, ['order' => 'id']))) {
            foreach ($elements as $element) {
                if (null !== ($elementInfo = $this->getContentElement((int) $element->id))) {
                    $includes[(int) $element->id] = $elementInfo;
                }
            }
        }

        return $includes;
    }

    private function getInsertTags(string $insertTag, int $id): array
    {
        $includes = [];

        $insertTags = InsertTagIndexModel::findByTagParams($insertTag, (string) $id, ['limit' => 10]);

        if (null === $insertTags) {
            return $includes;
        }

        foreach ($insertTags as $insertTag) {
            $include = [
                'crumbs' => [$insertTag->url],
                'title' => '<code>'.$insertTag->getInsertTagString().'</code>',
            ];

            if (!empty($insertTag->pid)) {
                $include['link'] = $this->router->generate('contao_backend', [
                    'do' => 'article',
                    'pn' => $insertTag->pid,
                    'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
                    'rt' => $this->tokenManager->getToken($this->tokenName),
                ]);
            }

            $includes[] = $include;
        }

        return $includes;
    }

    private function renderIncludes(?array $original, ?array $includes, string $class = null): ?string
    {
        if (empty($includes) && empty($original)) {
            return null;
        }

        // Add CSS and JS
        $GLOBALS['TL_CSS'][self::class] = 'bundles/includeinfo/be_styles.css';

        if (!Config::get('doNotCollapse')) {
            $GLOBALS['TL_MOOTOOLS'][self::class] = FrontendTemplate::generateScriptTag('bundles/includeinfo/be_javascript.js');
        }

        // Prepare the template
        $template = new BackendTemplate('be_includes');
        $template->original = $original;
        $template->includes = $includes;
        $template->class = $class;

        // Parse template
        return $template->parse();
    }
}

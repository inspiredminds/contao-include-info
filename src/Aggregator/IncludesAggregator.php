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
use Contao\DataContainer;
use Contao\DcaLoader;
use Contao\FormModel;
use Contao\FrontendTemplate;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\ThemeModel;
use Doctrine\DBAL\Connection;
use InspiredMinds\IncludeInfoBundle\Model\InsertTagIndexModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

final class IncludesAggregator
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly ContaoCsrfTokenManager $tokenManager,
        private readonly Connection $db,
        private readonly bool $enableIndex,
    ) {
    }

    public function renderIncludesForArticle(ArticleModel $article): string|null
    {
        $includes = $this->getArticleIncludes((int) $article->id);

        return $this->renderIncludes(null, $includes, ArticleModel::getTable());
    }

    public function renderIncludesForForm(FormModel $form): string|null
    {
        $includes = [...$this->getFormIncludes((int) $form->id), ...$this->getInsertTags('insert_form', (int) $form->id)];

        return $this->renderIncludes(null, $includes, FormModel::getTable());
    }

    public function renderIncludesForModule(ModuleModel $module): string|null
    {
        $includes = [...$this->getModuleIncludes((int) $module->id), ...$this->getInsertTags('insert_module', (int) $module->id)];

        return $this->renderIncludes(null, $includes, ModuleModel::getTable());
    }

    public function renderIncludesForContentElement(ContentModel $element): string|null
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
        } elseif ('form' === $type || 'ajaxform' === $type) {
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

    private function getContentElementIncludes(int $elementId, int|null $ignoreId = null): array
    {
        return [...$this->getContentElements('cteAlias', 'alias', $elementId, $ignoreId), ...$this->getInsertTags('insert_content', $elementId)];
    }

    private function getContentElement(int $contentElementId): array|null
    {
        // Get the content element
        if (!$element = ContentModel::findById($contentElementId)) {
            return null;
        }

        // Get the trail information
        if (!$trail = array_reverse($this->getTrailInfo($element->row(), $element->getTable()))) {
            return null;
        }

        // create breadcrumb
        return [
            'crumbs' => array_map(static fn (array $t) => $t['title'], \array_slice($trail, 0, \count($trail) - 1)),
            'title' => $trail[\count($trail) - 1]['title'],
            'link' => $this->getViewUrl($element->getTable(), $element->pid, $element->row()),
        ];
    }

    private function getArticleIncludes(int $articleId, int|null $ignoreId = null): array
    {
        return [...$this->getContentElements('articleAlias', 'article', $articleId, $ignoreId), ...$this->getInsertTags('insert_article', $articleId)];
    }

    private function getArticle(int $articleId): array|null
    {
        // Get the article
        if (!$article = ArticleModel::findById($articleId)) {
            return null;
        }

        // Get the parent pages
        if (!$pages = PageModel::findParentsById($article->pid)) {
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

    private function getForm(int $formId): array|null
    {
        // Get the form
        if (!$form = FormModel::findById($formId)) {
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

    private function getModule(int $moduleId): array|null
    {
        // Get the module
        $module = ModuleModel::findById($moduleId);

        if (null === $module) {
            return null;
        }

        // Get the theme
        $theme = ThemeModel::findById((int) $module->pid);

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
                'rt' => $this->tokenManager->getDefaultTokenValue(),
            ]),
        ];
    }

    private function getFormIncludes(int $formId, int|null $ignoreId = null): array
    {
        return [...$this->getContentElements('form', 'form', $formId, $ignoreId), ...$this->getContentElements('form', 'ajaxform', $formId, $ignoreId)];
    }

    private function getModuleIncludes(int $moduleId, int|null $ignoreId = null): array
    {
        $includes = $this->getContentElements('module', 'module', $moduleId, $ignoreId);

        if (null === ($module = ModuleModel::findById($moduleId))) {
            return $includes;
        }

        $theme = ThemeModel::findById((int) $module->pid);

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
                                'rt' => $this->tokenManager->getDefaultTokenValue(),
                            ]),
                        ];
                        break;
                    }
                }
            }
        }

        return $includes;
    }

    private function getContentElements(string $idField, string $elementType, int $id, int|null $ignoreId = null): array
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
        if (!$this->enableIndex) {
            return [];
        }

        $includes = [];

        $insertTags = InsertTagIndexModel::findByTagParams($insertTag, (string) $id, ['limit' => 10]);

        if (!$insertTags) {
            return $includes;
        }

        foreach ($insertTags as $insertTag) {
            $include = [
                'crumbs' => [$insertTag->url],
                'title' => '<code>'.$insertTag->getInsertTagString().'</code>',
            ];

            if ($insertTag->pid) {
                $include['link'] = $this->router->generate('contao_backend', [
                    'do' => 'article',
                    'pn' => $insertTag->pid,
                    'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
                    'rt' => $this->tokenManager->getDefaultTokenValue(),
                ]);
            }

            $includes[] = $include;
        }

        return $includes;
    }

    private function renderIncludes(array|null $original, array|null $includes, string|null $class = null): string|null
    {
        if ((null === $includes || [] === $includes) && (null === $original || [] === $original)) {
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

    private function getViewUrl(string $table, int $id, array $record): string|null
    {
        if (!$do = $this->findModuleFromTableId($record, $table)) {
            return null;
        }

        $query = [
            'do' => $do,
            'id' => $id,
            'table' => $table,
            'ref' => $this->requestStack->getCurrentRequest()->attributes->get('_contao_referer_id'),
        ];

        return $this->router->generate('contao_backend', $query);
    }

    private function findModuleFromTableId(array $record, string $table, array|null $filteredModules = null): string|null
    {
        $modules = [];

        foreach (null === $filteredModules ? $GLOBALS['BE_MOD'] : [$filteredModules] as $group) {
            foreach ($group as $do => $module) {
                if (\in_array($table, $module['tables'] ?? [], true)) {
                    $modules[$do] = $module;
                }
            }
        }

        if (1 === \count($modules)) {
            return array_keys($modules)[0];
        }

        if (isset($record['ptable'], $record['pid']) && ($precord = $this->db->fetchAssociative("SELECT * FROM {$record['ptable']} WHERE id = ?", [$record['pid']]))) {
            return $this->findModuleFromTableId($precord, $record['ptable'], $modules);
        }

        return array_keys($modules)[0] ?? null;
    }

    private function findParentFromRecord(array $record, string $table): array|null
    {
        (new DcaLoader($table))->load();

        $pid = (int) ($record['pid'] ?? null);

        if (!$pid) {
            return null;
        }

        if (($record['ptable'] ?? null) && ($GLOBALS['TL_DCA'][$table]['config']['dynamicPtable'] ?? null)) {
            $ptable = (string) $record['ptable'];
        } else {
            $ptable = $GLOBALS['TL_DCA'][$table]['config']['ptable'] ?? null;
        }

        if (!$ptable && DataContainer::MODE_TREE === $GLOBALS['TL_DCA'][$table]['list']['sorting']['mode']) {
            $ptable = $table;
        }

        if (!$ptable) {
            return null;
        }

        if (!$parentRecord = $this->db->fetchAssociative("SELECT * FROM {$ptable} WHERE id = ?", [$pid])) {
            return null;
        }

        return [
            'record' => $parentRecord,
            'table' => $ptable,
            'title' => $this->getTitle($parentRecord, $ptable),
        ];
    }

    private function getTrailInfo(array $record, string $table): array
    {
        $trail = [];

        while ($parent = $this->findParentFromRecord($record, $table)) {
            if ((int) $parent['record']['id'] === (int) $record['id']) {
                break;
            }

            $trail[] = $parent;

            $table = $parent['table'];
            $record = $parent['record'];
        }

        return $trail;
    }

    private function getTitle(array $record, string $table): string
    {
        (new DcaLoader($table))->load();

        $defaultSearchField = $GLOBALS['TL_DCA'][$table]['list']['sorting']['defaultSearchField'] ?? null;

        if ($defaultSearchField && ($label = $record[$defaultSearchField] ?? null)) {
            return trim(StringUtil::decodeEntities(strip_tags((string) $label)));
        }

        return $record['title'] ?? $record['name'] ?? $record['headline'] ?? null ?: $table.'.'.$record['id'];
    }
}

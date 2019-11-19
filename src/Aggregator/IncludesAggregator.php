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
use Contao\ContentModel;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class IncludesAggregator
{
    private $router;
    private $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function getContentElementIncludes(int $contentElementId): array
    {
        // Prepare array
        $includes = [];

        // Get all elements where this element is included
        if (null !== ($elements = ContentModel::findBy(['cteAlias = ?', "type = 'alias'"], $contentElementId, ['order' => 'id']))) {
            foreach ($elements as $element) {
                if (null !== ($elementInfo = $this->getContentElement((int) $element->id))) {
                    $includes[(int) $element->id] = $elementInfo;
                }
            }
        }

        return $includes;
    }

    public function getContentElement(int $contentElementId): ?array
    {
        // Get the content element
        $element = ContentModel::findByPk($contentElementId);

        if (null === $element) {
            return null;
        }

        // Get the parent article
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

    public function getArticleIncludes(int $articleId): array
    {
        // Prepare array
        $includes = [];

        // Get all elements where this element is included
        if (null !== ($elements = ContentModel::findBy(['articleAlias = ?', "type = 'article'"], [$articleId], ['order' => 'id']))) {
            foreach ($elements as $element) {
                if (null !== ($elementInfo = $this->getContentElement((int) $element->id))) {
                    $includes[(int) $element->id] = $elementInfo;
                }
            }
        }

        return $includes;
    }

    public function getArticle(int $articleId): array
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
}

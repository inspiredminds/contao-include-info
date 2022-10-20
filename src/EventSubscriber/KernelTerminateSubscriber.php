<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\EventSubscriber;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\InsertTags;
use Doctrine\DBAL\Connection;
use InspiredMinds\IncludeInfoBundle\EventListener\ReplaceInsertTagsListener;
use InspiredMinds\IncludeInfoBundle\Model\InsertTagIndexModel;
use Nyholm\Psr7\Uri;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelTerminateSubscriber implements EventSubscriberInterface
{
    private static $indexedInsertTags = [
        'insert_article',
        'insert_module',
        'insert_content',
        'insert_form',
    ];

    private $scopeMatcher;
    private $framework;
    private $db;
    private $insertTagListener;
    private $insertTagParser;

    public function __construct(ScopeMatcher $scopeMatcher, ContaoFramework $framework, Connection $db, ReplaceInsertTagsListener $insertTagListener, ?InsertTagParser $insertTagParser)
    {
        $this->scopeMatcher = $scopeMatcher;
        $this->framework = $framework;
        $this->db = $db;
        $this->insertTagListener = $insertTagListener;
        $this->insertTagParser = $insertTagParser;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle GET requests
        if (!$request->isMethod(Request::METHOD_GET)) {
            return;
        }

        // Only handle front end requests
        if (!$this->scopeMatcher->isFrontendRequest($request)) {
            return;
        }

        // Get normalized URL
        $url = $request->getSchemeAndHttpHost().strtok($request->getRequestUri(), '?');
        $url = str_replace($request->server->get('SCRIPT_NAME'), '', $url);
        $url = (string) (new Uri($url));

        // Discard any overly long URLs
        if (mb_strlen($url) > 2048) {
            return;
        }

        // Delete previous entries if response is not 2xx
        if (!$event->getResponse()->isSuccessful()) {
            $this->db->executeStatement('DELETE FROM tl_inserttag_index WHERE `url` = ?', [$url]);

            return;
        }

        // Do not index if framework was not initialized
        if (!$this->framework->isInitialized()) {
            return;
        }

        // Get the cached insert tags from the insert tag listener
        $insertTags = new InsertTags();

        if (null !== $this->insertTagParser) {
            $this->insertTagParser->replace('{{'.ReplaceInsertTagsListener::INDEX_INSERT_TAG.'}}');
        } else {
            (new InsertTags())->replace('{{'.ReplaceInsertTagsListener::INDEX_INSERT_TAG.'}}');
        }

        $insertTags = $this->insertTagListener->getInsertTags();

        // Index the insert tags for the current URL
        global $objPage;
        $pid = null !== $objPage ? (int) $objPage->id : null;
        $indexIds = [];

        foreach ($insertTags as $insertTag) {
            $flags = explode('|', $insertTag);
            $tag = array_shift($flags);
            $elements = explode('::', $tag);
            $tag = $elements[0];

            // Only index allowed insert tags
            if (!\in_array($tag, self::$indexedInsertTags, true)) {
                continue;
            }

            $params = \count($elements) > 1 ? $elements[1] : '';
            $flags = implode('|', $flags);

            $indexModel = InsertTagIndexModel::findOneByUrlTagParamsFlags($url, $tag, $params, $flags);

            if (null === $indexModel) {
                $indexModel = new InsertTagIndexModel();
                $indexModel->setRow([
                    'pid' => $pid,
                    'tstamp' => time(),
                    'url' => $url,
                    'tag' => $tag,
                    'params' => $params,
                    'flags' => $flags,
                ]);
            } else {
                $indexModel->tstamp = time();
            }

            $indexModel->save();
            $indexIds[] = (int) $indexModel->id;
        }

        // Delete all other indexed insert tags for this URL that have not been processed in the current request
        if (!empty($indexIds)) {
            $this->db->executeStatement('DELETE FROM tl_inserttag_index WHERE `url` = ? AND `id` NOT IN ('.implode(',', $indexIds).')', [$url]);
        } else {
            $this->db->executeStatement('DELETE FROM tl_inserttag_index WHERE `url` = ?', [$url]);
        }
    }
}

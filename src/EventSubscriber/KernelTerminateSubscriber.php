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
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\InsertTags;
use Doctrine\DBAL\Driver\Connection;
use InspiredMinds\IncludeInfoBundle\EventListener\ReplaceInsertTagsListener;
use InspiredMinds\IncludeInfoBundle\Model\InsertTagIndexModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
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
    private $logger;

    public function __construct(ScopeMatcher $scopeMatcher, ContaoFramework $framework, Connection $db, ReplaceInsertTagsListener $insertTagListener, LoggerInterface $logger)
    {
        $this->scopeMatcher = $scopeMatcher;
        $this->framework = $framework;
        $this->db = $db;
        $this->insertTagListener = $insertTagListener;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    public function onKernelTerminate(PostResponseEvent $event): void
    {
        if (!$this->framework->isInitialized() || !$this->scopeMatcher->isFrontendRequest($event->getRequest())) {
            return;
        }

        // Get the cached insert tags from the insert tag listener
        (new InsertTags())->replace('{{'.ReplaceInsertTagsListener::INDEX_INSERT_TAG.'}}');
        $insertTags = $this->insertTagListener->getInsertTags();

        // Index the insert tags for the current URL
        global $objPage;
        $pid = null !== $objPage ? (int) $objPage->id : null;
        $indexIds = [];
        $request = $event->getRequest();
        $url = $request->getSchemeAndHttpHost().strtok($request->getRequestUri(), '?');
        $url = str_replace($request->server->get('SCRIPT_NAME'), '', $url);

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
        $this->db->prepare('DELETE FROM tl_inserttag_index WHERE `url` = ? AND `id` NOT IN ('.implode(',', $indexIds).')')->execute([$url]);
    }
}

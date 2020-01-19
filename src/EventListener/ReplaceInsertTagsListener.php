<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

class ReplaceInsertTagsListener
{
    public const INDEX_INSERT_TAG = 'include_info_index';

    private $db;
    private $requestStack;
    private $insertTags = [];

    public function __invoke(
        string $insertTag,
        bool $useCache,
        string $cachedValue,
        array $flags,
        array $tags,
        array $cache
    ): bool {
        if (self::INDEX_INSERT_TAG === $insertTag) {
            $this->insertTags = array_keys($cache);
        }

        /*

            global $objPage;
            $pid = null !== $objPage ? (int) $objPage->id : null;
            $indexIds = [];
            $request = $this->requestStack->getCurrentRequest();
            $url = strtok($request->getRequestUri(), '?');
            $url = str_replace($request->server->get('SCRIPT_NAME'), '', $url);

            throw new \Exception(impode(', ', array_keys($cache)));
            foreach (array_keys($cache) as $it) {
                $flags = explode('|', $it);
                $tag = array_shift($flags);
                $elements = explode('::', $tag);
                $tag = $elements[0];

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

            $this->db->prepare('DELETE FROM tl_inserttag_index WHERE `url` = ? AND `id` NOT IN ('.implode(',', $indexIds).')')->execute([$url]);
        }
*/
        return false;
    }

    public function getInsertTags(): array
    {
        return $this->insertTags;
    }
}

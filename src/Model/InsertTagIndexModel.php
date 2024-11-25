<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

class InsertTagIndexModel extends Model
{
    protected static $strTable = 'tl_inserttag_index';

    public static function findOneByUrlTagParamsFlags(string $url, string $tag, string $params, string $flags, array $options = []): Model|null
    {
        $t = static::getTable();

        $columns = [
            "$t.url = ?",
            "$t.tag = ?",
            "$t.params = ?",
            "$t.flags = ?",
        ];

        $values = [
            $url,
            $tag,
            $params,
            $flags,
        ];

        return static::findOneBy($columns, $values, $options);
    }

    public static function findByTagParams(string $tag, string $params, array $options = []): Collection|null
    {
        $t = static::getTable();

        $columns = [
            "$t.tag = ?",
            "$t.params = ?",
        ];

        $values = [
            $tag,
            $params,
        ];

        return static::findBy($columns, $values, $options);
    }

    public function getInsertTagString(): string
    {
        $insertTag = '{{'.$this->tag;

        if (!empty($this->params)) {
            $insertTag .= '::'.$this->params;
        }

        if (!empty($this->flags)) {
            $insertTag .= '|'.$this->flags;
        }

        return $insertTag.'}}';
    }
}

<?php

namespace App\Utility;

use Illuminate\Http\Request;

/**
 * Paging Class responsible for api paging
 * @package App\Utility
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class Paging
{
    /**
     * @var int Paging::DEFAULT_OFFSET default offset
     */
    const DEFAULT_OFFSET = 0;

    /**
     * @var int Paging::DEFAULT_COUNT default count
     */
    const DEFAULT_COUNT = 5;

    /**
     * @var Request $request instance from Request object
     */
    private $request;

    /**
     * @var int $previousOffset previous offset
     */
    private $previousOffset;

    /**
     * @var int $currentOffset current offset
     */
    private $currentOffset;

    /**
     * @var int $nextOffset next offset
     */
    private $nextOffset;

    /**
     * @var int $count limit
     */
    public $count;

    /**
     * Paging constructor.
     * @param Request $request
     * @param int|null $count
     * @param int|null $offset
     */
    public function __construct(Request $request, int $count = null, int $offset = null)
    {
        $this->request        = $request;
        $this->currentOffset  = $offset ? $offset : self::DEFAULT_OFFSET;
        $this->count          = $count ? $count : self::DEFAULT_COUNT;
        $this->previousOffset = ($this->currentOffset - $this->count);
        $this->nextOffset     = ($this->currentOffset + $this->count);
    }

    /**
     * Get paging object
     * @return \stdClass
     */
    public function getPaging() : \stdClass
    {
        $paging = new \stdClass();
        if ($this->previousOffset < 0) {
            $paging->previous = null;
            $paging->previousUrl = null;
        } else {
            $paging->previous = new \stdClass();
            $paging->previous->from = $this->previousOffset;
            $paging->previous->count = $this->count;
            $paging->previousUrl = $this->buildPagingUrl($paging->previous->count, $paging->previous->from);
        }

        if ($this->nextOffset == null) {
            $paging->next = null;
            $paging->nextUrl = null;
        } else {
            $paging->next = new \stdClass();
            $paging->next->from = $this->nextOffset;
            $paging->next->count = $this->count;
            $paging->nextUrl = $this->buildPagingUrl($paging->next->count, $paging->next->from);
        }
        return $paging;
    }

    /**
     * Set next offset to null
     */
    public function noNextPaging()
    {
        $this->nextOffset = null;
    }

    /**
     * Generate paging url
     * @param int $count
     * @param int $offset
     * @return string
     */
    private function buildPagingUrl(int $count, int $offset) : string
    {
        $url = $this->request->url();
        $queryParams = $this->request->all();
        $queryParams["page"] = ($offset / $count) + 1;
        $queryParams["limit"] = $count;
        $url .= '?' . http_build_query($queryParams);
        return $url;
    }
}
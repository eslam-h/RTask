<?php

namespace App\Http\Controllers\Abstracts;

use App\Http\Controllers\Controller;
use App\Utility\Paging;
use Illuminate\Http\Request;

/**
 * AbstractController Class abstract controller shared across all controllers
 * @package App\Http\Controllers\Abstracts
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
abstract class AbstractController extends Controller
{
    /**
     * @var Request $request instance from Request object
     */
    protected $request;

    /**
     * AbstractController constructor.
     * @param Request $request instance from Request object
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get pagin instance
     * @param int|null $count
     * @param int|null $offset
     * @return Paging
     */
    protected function initializePaging(int $count = null, int $offset = null)
    {
        return new Paging($this->request, $count, $offset);
    }
}
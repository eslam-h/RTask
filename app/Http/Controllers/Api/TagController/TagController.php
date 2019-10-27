<?php

namespace App\Http\Controllers\Api\TagController;

use App\Http\Controllers\Abstracts\AbstractController;
use Dev\Domain\Service\TagService\TagService;
use Illuminate\Http\Request;
use Dev\Infrastructure\Models\TripModels\TripTagModel;
use Validator;

/**
 * TagController Class for returning app tag list
 * @package App\Http\Controllers\Api\TagController
 * @author Mohamad El-Wakeel <m.elwakeel@shiftebusiness.com>
 */
class TagController extends AbstractController
{
    /**
     * @var TagService $tagService instance from TagService
     */
    private $tagService;

    /**
     * TagController constructor.
     * @param Request $request
     * @param TagService $tagService
     */
    public function __construct(Request $request, TagService $tagService)
    {
        parent::__construct($request);
        $this->tagService = $tagService;
    }

    public function list(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->tagService->setRequestObject($requestedObject);
        }

        $tags = TripTagModel::join('tags', 'trip-tags.tag-id', '=', 'tags.id')
                            ->select('tags.id', 'tags.name')
                            ->distinct()
                            ->orderBy('tags.name', 'asc')
                            ->get();
        if ($tags) {
        $responseData["data"] = $tags;
        return response()->json($responseData, 200);
        } else {
             return response()->json([], 204);
        }
    }
}
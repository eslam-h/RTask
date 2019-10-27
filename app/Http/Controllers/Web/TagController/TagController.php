<?php

namespace App\Http\Controllers\Web\TagController;

use App\Http\Controllers\Abstracts\AbstractController;
use App\Http\Requests\TagRequest\TagPostRequest;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Domain\Entity\Tag;
use Dev\Domain\Entity\TagTranslation;
use Dev\Domain\Service\SystemLanguageService\SystemAvailableLanguageService;
use Dev\Domain\Service\TagService\TagService;
use Dev\Domain\Service\TagService\TagTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * TagController Class responsible for all handling all requests related to tag
 * @package App\Http\Controllers\Web\TagController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class TagController extends AbstractController
{
    /**
     * @var TagService $tagService instance from TagService
     */
    private $tagService;

    /**
     * @var int page count
     */
    private $count = 30;

    /**
     * @var SystemAvailableLanguageService $systemAvailableLanguageService instance from SystemAvailableLanguageService
     */
    private $systemAvailableLanguageService;

    /**
     * @var TagTranslationService $tagTranslationService instance from TagTranslationService
     */
    private $tagTranslationService;

    /**
     * TagController constructor.
     * @param TagService $tagService instance from TagService
     * @param TagTranslationService $tagTranslationService instance from TagTranslationService
     * @param SystemAvailableLanguageService $systemAvailableLanguageService instance from SystemAvailableLanguageService
     * @param Request $request instance from Request
     */
    public function __construct(
        Request $request,
        TagService $tagService,
        TagTranslationService $tagTranslationService,
        SystemAvailableLanguageService $systemAvailableLanguageService
    ) {
        parent::__construct($request);
        $this->tagService = $tagService;
        $this->systemAvailableLanguageService = $systemAvailableLanguageService;
        $this->tagTranslationService = $tagTranslationService;
    }

    /**
     * Display creation form of the tag
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayCreateFormAction()
    {
        $actionUrl = "/tags/create";
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }
        $data = [
            "actionUrl" => $actionUrl,
            "availableLanguages" => $allAvailableLanguages
        ];
        return view("front.tag.tag-form", $data);
    }

    /**
     * Add new tag and its translations action
     * @param TagPostRequest $request instance from TagPostRequest
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function addNewTagAction(TagPostRequest $request)
    {
        $validated = $request->validated();
        $data = $request->all();
        $tag = $this->mapDataToTagEntity($data);
        try {
            $createdTag = $this->tagService->addNewTag($tag);
        } catch (InvalidArgumentException $invalidExc) {

        }
        if ($tag->translations) {
            /**
             * @var TagTranslation $tagTranslation
             */
            foreach ($tag->translations as $tagTranslation) {
                $tagTranslation->tag = $createdTag;
                try {
                    $this->tagTranslationService->addNewTagTranslation($tagTranslation);
                } catch (InvalidArgumentException $invalidExc) {

                }
            }
        }
        return redirect("tags/list");
    }

    /**
     * Display creation form of the tag
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayEditFormAction(Request $request, int $id)
    {
        $actionUrl = "/tags/{$id}/edit";
        try {
            $tagItem = $this->tagService->getTagWithId($id);
        } catch (NotFoundException $notFoundException) {
            return redirect("tags/list");
        }
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }
        $data = [
            "actionUrl" => $actionUrl,
            "availableLanguages" => $allAvailableLanguages,
            "defaultName" => $tagItem->name,
            "translations" => $tagItem->translations ? $tagItem->translations : [],
        ];

        return view("front.tag.tag-form", $data);
    }

    /**
     * Update tag and its translations action
     * @param TagPostRequest $request instance from TagPostRequest
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateTagAction(TagPostRequest $request, int $id)
    {
        try {
            $tagItem = $this->tagService->getTagWithId($id);
        } catch (NotFoundException $notFoundException) {
            return redirect("tags/list");
        }
        $validated = $request->validated();
        $data = $request->all();
        $tag = $this->mapDataToTagEntity($data);
        $tag->id = $id;
        try {
            $updatedTag = $this->tagService->updateTag($tag);
        } catch (InvalidArgumentException $invalidExc) {

        }
        if ($tag->translations) {
            /**
             * @var TagTranslation $tagTranslation
             */
            foreach ($tag->translations as $tagTranslation) {
                $tagTranslation->tag = $tagItem;
                $criteria = [
                    "tag-id" => $tag->id,
                    "language-code" => $tagTranslation->languageCode
                ];
                $existedTagTranslation = $this->tagTranslationService->getTagTranslationWithCriteria($criteria);
                if ($existedTagTranslation) {
                    if ($tagTranslation->name) {
                        try {
                            $this->tagTranslationService->updateTagTranslation($tagTranslation);
                        } catch (InvalidArgumentException $invalidExc) {

                        }
                    } else {
                        $this->tagTranslationService->deleteTagTranslation($existedTagTranslation[0]->id);
                    }
                } else {
                    try {
                        $this->tagTranslationService->addNewTagTranslation($tagTranslation);
                    } catch (InvalidArgumentException $invalidExc) {

                    }
                }
            }
        }
        return redirect("tags/list");
    }

    /**
     * Display listing view of the tag
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listTagAction(Request $request)
    {
        $data = $request->all();
        $criteria = [];

        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;

        if (isset($data["name-search"])) {
            $criteria["name-filter"] = $data["name-search"];
        }

        $tags = $this->tagService->getTagWithCriteria($criteria, $count, $offset);
        $paging = $this->tagService->getTagLinks($criteria, $count, $offset);
        $data = [
            "tags" => $tags,
            "paging" => $paging,
            "searchInput" => isset($data['name-search']) ? $data['name-search'] : ''

        ];
        return view("front.tag.tag-list", $data);
    }

    /**
     * Delete tag item
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteTagAction(Request  $request, int $id)
    {
        $this->tagService->deleteTag($id);
        return redirect("tags/list");
    }

    /**
     * Map data to tag instance
     * @param array $data
     * @return Tag
     */
    private function mapDataToTagEntity(array $data)
    {
        $tag = new Tag();
        if (isset($data["default-name"])) {
            $tag->name = $data["default-name"];
        }
        if (isset($data["name-translation"]) && !empty($data["name-translation"])) {
            foreach ($data["name-translation"] as $langCode => $nameTranslation) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->name = $nameTranslation;
                $tagTranslation->languageCode = $langCode;
                $tag->translations[] = $tagTranslation;
            }
        }
        return $tag;
    }

    /**
     * @param array $criteria
     * @param int|null $count
     * @param int|null $offset
     * @return \stdClass
     */
    private function getPaging(array $criteria, int $count = null, int $offset = null)
    {
        $paging = parent::initializePaging($count, $offset);
        $tagWithCriteria = $this->tagService->getTagWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($tagWithCriteria)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}
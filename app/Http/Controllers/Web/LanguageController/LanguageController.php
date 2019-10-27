<?php

namespace App\Http\Controllers\Web\LanguageController;

use Illuminate\Http\Request;
use App\Http\Controllers\Abstracts\AbstractController;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Domain\Entity\Language;
use Dev\Domain\Service\LanguageService\LanguageService;

class LanguageController extends AbstractController
{
    /**
     * @var LanguageService $languageService instance from language service
     */
    private $languageService;

    /**
     * @var int page count
     */
    private $count = 30;

    /**
     * LanguageController constructor.
     * @param Request $request
     * @param LanguageService $languageService
     */
    public function __construct(Request $request, LanguageService $languageService)
    {
        parent::__construct($request);
        $this->languageService = $languageService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list(Request $request)
    {
        $data = $request->all();
        $criteria = [];

        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;

        if (isset($data["name-search"])) {
            $criteria["name-filter"] = $data["name-search"];
        }

        $languages = $this->languageService->getLanguagesWithCriteria($criteria, $count, $offset);
        $paging = $this->languageService->getLanguagesLinks($criteria, $count, $offset);

        $results   = [];
        if ($languages) {
            foreach ($languages as $lang) {
                $results[] = array('id' => $lang->id, 'name' => $lang->name, 'code' => $lang->code);
            }
        }
        $data = [
            "languages" => $results,
            "paging" => $paging,
            "searchInput" => isset($data['name-search']) ? $data['name-search'] : ''
        ];
        return view("front.language.list", $data);
    }

    public function form($id = '')
    {
        $data = [];
        if (!empty($id)) {
            $data['id'] = $id;
            try {
                $language = $this->languageService->getLanguageWithId($id);
                $data['defaultName'] = $language->name ;
                $data['code'] = $language->code ;
            } catch (NotFoundException $notFoundException) {
                return redirect("language/list");
            }
        }
        $actionUrl         = "/language/save";
        $data['actionUrl'] = $actionUrl ;
        return view("front.language.form", $data);
    }

    public function save(Request $request)
    {
        $data = $request->all();
        $id = isset($data['id'])? $data['id'] : '';
        if (!empty($id)) {
            try {
                $language = $this->languageService->getLanguageWithId($id);
            } catch (NotFoundException $notFoundException) {
                return redirect("language/list");
            }

        } else {
            $language = new Language();
        }
        $language->name = $data["default-name"];
        $language->code = $data["code"];
        $language->status = 0;
        if (!empty($id)) {
            $savedLanguage =  $this->languageService->updateLanguage($language);
        } else {
            $savedLanguage =  $this->languageService->addNewLanguage($language);
        }
        
        return redirect("language/list");
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $language = new Language();
        $language->name = $data["default-name"];
        $language->code = 'bb';
        $language->status = 0;

        try {
            $createdLanguage =  $this->languageService->addNewLanguage($language);
        } catch (Exception $e) {
            
        }
    }

    public function delete($id)
    {
        $this->languageService->deleteLanguage($id);
        return redirect("language/list");
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
        $languagesWithCriteria = $this->languageService->getLanguagesWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($languagesWithCriteria)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}
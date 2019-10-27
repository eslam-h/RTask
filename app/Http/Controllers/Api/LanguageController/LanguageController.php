<?php

namespace App\Http\Controllers\Api\LanguageController;

use App\Http\Controllers\Abstracts\AbstractController;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Domain\Entity\Language;
use Dev\Domain\Service\LanguageService\LanguageService;
use Illuminate\Http\Request;

/**
 * LanguageController Class for returning app languages & translated keywords
 * @package App\Http\Controllers\Api\LanguageController
 * @author Mohamad El-Wakeel <m.elwakeel@shiftebusiness.com>
 */
class LanguageController extends AbstractController
{
    /**
     * @var LanguageService $languageService instance from language service
     */
    private $languageService;

    /**
     * @var string $langMobVisitor path to language for mobile visitor
     */
    private $langMobVisitor = 'mobile/visitor';

    /**
     * LanguageController constructor.
     * @param languageService $languageService instance from language service
     * @param Request $request instance from Request
     */
	public function __construct(Request $request, LanguageService $languageService)
    {
        parent::__construct($request);
        $this->languageService = $languageService;
    }

    /**
     * List of languages
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->languageService->setRequestObject($requestedObject);
        }
		$languages = $this->languageService->getPublishedLanguages(json_decode($request->getContent(), true));
        if (empty($languages)) {
            return response('',204);
        } else {
            $responseData['data'] = $languages;
            return response()->json($responseData, 200);   
        }
    }

    public function downloadJson($langcode)
    {
        $filePath =  resource_path("lang/{$langcode}/".$this->langMobVisitor."/{$langcode}.json");
        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            //return '';
            $errors["errors"]["message"] = __("File not exist.");
            return response($errors, 404);
        }
    }
}
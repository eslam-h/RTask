<?php

namespace App\Http\Controllers\Web\CurrencyController;

use App\Http\Controllers\Abstracts\AbstractWebController;
use Dev\Domain\Entity\Currency;
use Dev\Domain\Service\CurrencyService\CurrencyService;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Validator;

/**
 * CurrencyController Class responsible for currency actions
 * @package App\Http\Controllers\Web\CurrencyController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class CurrencyController extends AbstractWebController
{
    /**
     * @var CurrencyService $currencyService instance from CurrencyService
     */
    private $currencyService;

    /**
     * @var int page count
     */
    private $count = 30;

    /**
     * CurrencyController constructor.
     * @param CurrencyService $currencyService
     * @param Request $request
     */
    public function __construct(
        Request $request,
        CurrencyService $currencyService
    ) {
        parent::__construct($request);
        $this->currencyService = $currencyService;
    }

    /**
     * display currency create form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayCurrencyCreationForm()
    {
        $actionUrl = "/currency/create";
        $data = [
            "actionUrl" => $actionUrl
        ];
        return view("front.currency.currency-form", $data);
    }

    /**
     * create a new currency action
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createNewCurrencyAction(Request $request)
    {
        $messages = [
            "currency.required" => "The currency name field is required.",
            "currency.unique" => "The currency name must be unique.",
            "code.unique" => "The currency code must be unique.",
            "code.required" => "The currency code field is required.",
        ];
        $validator = Validator::make($request->all(), [
            "currency" => "required|unique:currencies,currency",
            "code" => "required|unique:currencies,code",
        ], $messages);
        if ($validator->fails()) {
            return redirect('currency/create')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $request->all();
        $currencyItem = $this->mapDataToCurrencyEntity($data);
        try {
             $this->currencyService->addNewCurrency($currencyItem);
        } catch (InvalidArgumentException $argumentException) {
        }
        return redirect("currency/list");
    }

    /**
     * get all saved currencies in the database
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listCurrencies(Request $request)
    {
        $data = $request->all();
        $criteria = [];

        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;

        if (isset($data["name-search"])) {
            $criteria["currency-filter"] = $data["name-search"];
        }

        $currencies = $this->currencyService->getCurrenciesWithCriteria($criteria, $count, $offset);
        $paging = $this->currencyService->getCurrenciesLinks($criteria, $count, $offset);

        $data = [
            "entities" => $currencies,
            "paging" => $paging,
            "searchInput" => isset($data['name-search']) ? $data['name-search'] : ''
        ];

        return view("front.currency.list", $data);
    }


    /**
     * edit currency action
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateCurrencyAction(Request $request, $id)
    {
        try{
            $currencyItem = $this->currencyService->getCurrencyWithId($id);
        }catch (NotFoundException $notFoundException){
            return redirect("currency/list");
        }
        $messages = [
            "currency.required" => "The currency name field is required.",
            "code.required" => "The currency code field is required.",
            "currency.unique" => "The currency name must be unique.",
            "code.unique" => "The currency code must be unique.",
        ];
        $validator = Validator::make($request->all(), [
            "currency" => "required|unique:currencies,currency,".$currencyItem->id,
            "code" => "required|unique:currencies,code,".$currencyItem->id,
        ], $messages);

        if ($validator->fails()) {
            return redirect('/currency/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $request->all();
        $currency = new Currency();
        $currency->id = $id;
        $currency->currency = $data['currency'];
        $currency->code = $data['code'];

        try{
            $this->currencyService->updateCurrency($currency);
        }catch (InvalidArgumentException $invalidArgumentException){
            return redirect('/currency/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        }
        return redirect("currency/list");
    }

    /**
     * display currency edit form
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function displayCurrencyEditForm($id)
    {
        try{
            $currencyItem = $this->currencyService->getCurrencyWithId($id);
        }catch (NotFoundException $otFoundException ){
            return redirect("currency/list");
        }
        $actionUrl = "/currency/$id/update";
        $data = [
            "currency" => $currencyItem->currency,
            "code" => $currencyItem->code,
            "actionUrl" => $actionUrl,
        ];
        return view("front.currency.currency-form", $data);
    }

    /**
     * delete currency action
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteCurrencyAction($id)
    {
        try {
            $this->currencyService->deleteCurrency($id);
        } catch (QueryException $queryException) {
            switch ($queryException->getCode()) {
                case 23000:
                    $message = "Currency can not be deleted as it is being used by another instance in the system";
                    return redirect("currency/list")->with("delete-currency-error", $message);
                    break;
            }
        }
        return redirect("currency/list");
    }

    /**
     * Map request data to @see Currency instance
     * @param array $data
     * @return Currency
     */
    private function mapDataToCurrencyEntity(array $data) : Currency
    {
        $currency = new Currency();
        if (isset($data["currency"])) {
            $currency->currency = $data["currency"];
        }
        if (isset($data["code"])) {
            $currency->code = $data["code"];
        }
        return $currency;
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
        $currenciesWithCriteria = $this->currencyService->getCurrenciesWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($currenciesWithCriteria)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}
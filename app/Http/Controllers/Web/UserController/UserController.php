<?php

namespace App\Http\Controllers\Web\UserController;

use App\Http\Controllers\Abstracts\AbstractWebController;
use Dev\Application\Exceptions\AuthenticationFailureException;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Domain\Entity\User;
use Dev\Domain\Service\UserService\UserService;
use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers\Web\UserController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class UserController extends AbstractWebController
{
    /**
     * @var UserService $userService instance from UserService
     */
    private $userService;

    /**
     * UserController constructor.
     * @param Request $request instance from Request
     * @param UserService $userService instance from UserService
     */
    public function __construct(Request $request, UserService $userService)
    {
        parent::__construct($request);
        $this->userService = $userService;
    }

    /**
     * Validate user login data
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     */
    public function loginAction(Request $request)
    {
        $data = $request->all();
        $validatedData = $request->validate([
            "email" => "required",
            "password" => "required"
        ]);
        $loggedInUser = new User();
        $loggedInUser->email = $data["email"];
        $loggedInUser->setPassword($data["password"]);
        try {
            $authenticatedUser = $this->userService->authenticateWebUser($loggedInUser);
        } catch (NotFoundException $notFoundException) {
            return redirect("/login");
        } catch (AuthenticationFailureException $authenticationFailureException) {
            return redirect("/login");
        }
        $request->session()->put("webAuthUser", $authenticatedUser);
        return redirect()->route("home");
    }
}
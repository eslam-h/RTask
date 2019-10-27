<?php

namespace App\Http\Controllers\Web\UserController;

use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use App\Http\Controllers\Abstracts\AbstractWebController;
use Dev\Domain\Entity\Customer;
use Dev\Domain\Entity\Permission;
use Dev\Domain\Entity\Role;
use Dev\Domain\Entity\User;
use Dev\Domain\Service\RoleService\RoleService;
use Dev\Domain\Service\UserService\UserInvitationService;
use Dev\Domain\Service\UserService\UserService;
use Dev\Domain\Utility\CodeGenerator;
use Illuminate\Http\Request;
use Validator;

/**
 * Class UserInvitationController responsible for all actions related to user invitation
 * @package App\Http\Controllers\Web\UserController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class UserInvitationController extends AbstractWebController
{
    /**
     * @var UserService $userService instance from UserService
     */
    private $userService;

    /**
     * @var UserInvitationService $userInvitationService instance from UserInvitationService
     */
    private $userInvitationService;
    /**
     * @var int page count
     */
    private $count = 30;

    /**
     * @var RoleService $roleService instance from RoleService
     */
    private $roleService;

    /**
     * UserInvitationController constructor.
     * @param Request $request
     * @param UserService $userService
     * @param RoleService $roleService
     * @param UserInvitationService $userInvitationService
     */
    public function __construct(
        Request $request,
        UserService $userService,
        RoleService $roleService,
        UserInvitationService $userInvitationService
    ) {
        parent::__construct($request);
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->userInvitationService = $userInvitationService;
    }

    /**
     * display user invitation create form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayUserInvitationCreationForm()
    {
        $actionUrl = "/user/create";
        $roles = $this->getAllRoles();
        $data = [
            "actionUrl" => $actionUrl,
            "roles" => $roles,
        ];
        return view("front.user.user-form", $data);
    }

    /**
     * create a new user invitation action
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createNewUserInvitationAction(Request $request)
    {
        $messages = [
            "first-name.required" => "The first name field is required.",
            "email.required" => "The email field is required.",
            "email.email" => "This Email address is not valid.",
            "email.unique" => "This Email already exist.",
            "phone.regex" => "The Phone number field must contain only numbers.",
            "phone.min" => "The Phone number must be of minimum 10 digits.",
            "phone.max" => "The Phone number must be of maximum 11 digits.",
            "role-id.required" => "The user role is required.",
            "role-id.exists" => "The user role does not exist.",
        ];

        $validator = Validator::make($request->all(), [
            "first-name" => "required",
            "email" => "required|email|unique:users,email",
            "phone" => "|regex:/^[0-9 ]+$/|min:12|max:13",
            "role-id" => "required|exists:roles,id"
        ], $messages);

        if ($validator->fails()) {
            return redirect('user/create')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        $data["customer-id"] = 1;
        $user = $this->mapDataToUserEntity($data);
        try {
           $createdUser = $this->userService->addNewUser($user);
        } catch (InvalidArgumentException $argumentException) {
            dd('err');
        }

        $CodeGenerator = new CodeGenerator($createdUser->id);
        $code = $CodeGenerator->generateCode();
        $registeredUser = new User();
        $registeredUser->id = $createdUser->id;
        $registeredUser->confirmationCode = $code;
        $expireTime = date("H:i:s", strtotime("+1 hours"));
        $registeredUser->confirmationCodeExpiredAt = $expireTime;
        try{
            $this->userService->updateUser($registeredUser);
        } catch (InvalidArgumentException $exception){
            dd('err');
        }
        $mailSending =  $this->userInvitationService->emailVerificationCode($code, $createdUser->email, $createdUser->firstName);
        if ($mailSending){
            return redirect("user/list");
        }
    }

    /**
     * listing all invited users
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listUsers(Request $request)
    {
        $data = $request->all();
        $criteria = [];

        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;

        if (isset($data["name-search"])) {
            $criteria["name-filter"] = $data["name-search"];
        }

        $userWithCriteria = $this->userService->getUserWithCriteria($criteria, $count, $offset);
        $paging = $this->userService->getPaginationLinks($criteria, $count, $offset);
        $data = [
            "entities" => $userWithCriteria,
            "paging" => $paging,
            "searchInput" => isset($data['name-search']) ? $data['name-search'] : ''
        ];

        return view("front.user.list", $data);
    }

    /**
     * delete user action
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteInvitedUserAction($id)
    {
        $this->userService->deleteUser($id);
        return redirect("user/list");
    }

    /**
     * display city edit form
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayInvitedUserEditForm($id)
    {
        try{
            $userWithId = $this->userService->getUserWithId($id);
        }catch (NotFoundException $otFoundException ){
            return redirect("user/list");
        }

        $actionUrl = "/user/$id/update";
        $roles = $this->getAllRoles();

        $data = [
            "firstName" => $userWithId->firstName,
            "familyName" => $userWithId->familyName,
            "phone" => $userWithId->phone,
            "email" =>$userWithId->email,
            "actionUrl" => $actionUrl,
            "role"=> $userWithId->role,
            "roles" => $roles ? $roles :[],
        ];
        return view("front.user.user-form", $data);
    }

    /**
     * update city action
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateInvitedUserAction(Request $request, $id)
    {
        try{
            $userWithId = $this->userService->getUserWithId($id);
        }catch (NotFoundException $notFoundException){
            return redirect("user/list");
        }
        $messages = [
            "first-name.required" => "The first name field is required.",
            "email.required" => "The email field is required.",
            "email.email" => "This Email address is not valid.",
            "phone.regex" => "The Phone number field must contain only numbers.",
            "phone.min" => "The Phone number must be of minimum 10 digits.",
            "phone.max" => "The Phone number must be of maximum 11 digits.",
            "role-id.required" => "The user role is required.",
            "role-id.exists" => "The user role does not exist.",
        ];
        $validator = Validator::make($request->all(), [
            "first-name" => "required",
            "email" => "required|email|unique:users,email,".$userWithId->id,
            "phone" => "nullable|regex:/^[0-9 ]+$/|min:12|max:13",
            "role-id" => "required|exists:roles,id"
        ], $messages);

        if ($validator->fails()) {
            return redirect('/user/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $toUserEntity = $this->mapDataToUserEntity($data);
        $toUserEntity->id = $id;

        if($data['email'] != $userWithId->email) {
            $CodeGenerator = new CodeGenerator($id);
            $code = $CodeGenerator->generateCode();
            $toUserEntity->isConfirmed = 0;
            $toUserEntity->confirmationCode = $code;
            $expireTime = date("H:i:s", strtotime("+1 hours"));
            $toUserEntity->confirmationCodeExpiredAt = $expireTime;
            try{
                $this->userService->updateUser($toUserEntity);
            }catch (InvalidArgumentException $invalidArgumentException){
            }

            $email = $this->userInvitationService->emailVerificationCode($code, $data['email'], $data['first-name']);
            if ($email){
                return redirect("user/list");
            }
        }

        $toUserEntity->isConfirmed = $userWithId->isConfirmed;
        try{
            $this->userService->updateUser($toUserEntity);
        }catch (InvalidArgumentException $invalidArgumentException){
        }

        return redirect("user/list");
    }


    /**
     * @return array
     */
    private function getAllRoles() : array
    {
        $allRoles = $this->roleService->getAllRoles();
        $entities = [];
        /**
         * @var Role $role
         */
        foreach ($allRoles as $role) {
            $entities[$role->id] = $role->role;
        }
        return $entities;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayUserVerificationPage()
    {
        return view("front.verification");
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function verifyAndUpdateUserAction(Request $request)
    {
        $passwordPattern = "/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/";
        $messages = [
            "email.required" => "The email field is required.",
            "email.email" => "This Email address is not valid.",
            "email.exists" => "No user registered with this Email address.",
            "code.digits" => "The verification code size is incorrect.",
            "code.required" => "The verification code is required.",
            "password.required" => "The password field is required.",
            "password.same" => "The passwords fields does not match.",
            "password.max" => "Must define user password with 8 chars minimum and 20 chars maximum",
            "password.min" => "Must define user password with 8 chars minimum and 20 chars maximum",
            "confirm-password.required" => "Confirm password fields is required.",
        ];

        $validator = Validator::make($request->all(), [
            "email" => "required|email|exists:users,email",
            "code" => "required|numeric|digits:5",
            "password" => "required|max:20|min:8",
            "confirm-password" => "required"
        ], $messages);

        if ($validator->fails()) {
            return redirect('user-invitations')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        if ($data['password'] != $data['confirm-password']){
            return redirect('user-invitations')->with('error', 'The passwords fields does not match.')->withInput();;
        }
        $userCriteria = [
            "email" => $data['email'],
            "confirmation_code" => $data['code']
        ];

        $result = $this->userService->getUserWithCriteria($userCriteria);
        if (empty($result)) {
            return redirect('user-invitations')->with('error', 'Verification code is invalid.')->withInput();
        }
        $userRecord = $result[0];
        $currentTime = date("H:i:s");
        if ($currentTime > $userRecord->confirmationCodeExpiredAt) {
            return redirect('user-invitations')->with('error', 'Verification code has been expired, You can request a new one.')->withInput();
        }
        $data = $request->all();
        $toUserEntity = $this->mapDataToUserEntity($data);
        if (!preg_match($passwordPattern, $toUserEntity->getPlainPassword())) {
            return redirect('user-invitations')->with('error', "Must define user password that contains alphanumeric")->withInput();
        }
        $toUserEntity->id = $userRecord->id;;
        $toUserEntity->isConfirmed = 1;
        $toUserEntity->confirmationCode = '';
        $toUserEntity->confirmationCodeExpiredAt = 0;

        try{
            $this->userService->updateUser($toUserEntity);
        } catch (InvalidArgumentException $exception){

        }
        return redirect('/login')->with('status', 'You have registered successfully, Now you can login.');

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function resendUserInvitationCode(Request $request)
    {
        $messages = [
        "email.required" => "The email field is required.",
        "email.email" => "This Email address is not valid.",
        "email.exists" => "This Email address does not exist.",
        ];
        $validator = Validator::make($request->all(), [
            "email" => "required|email|exists:users,email",
        ], $messages);

        if ($validator->fails()) {
            return redirect('/user-invitations')
                ->withErrors($validator)
                ->withInput();
        }

        $criteria = ["email" => $request['email']];
        $user = $this->userService->getUserWithCriteria($criteria);
        $CodeGenerator = new CodeGenerator($user[0]->id);
        $code = $CodeGenerator->generateCode();
        $registeredUser = new User();
        $registeredUser->id = $user[0]->id;
        $registeredUser->isConfirmed = 0;
        $registeredUser->confirmationCode = $code;
        $expireTime = date("H:i:s", strtotime("+1 hours"));
        $registeredUser->confirmationCodeExpiredAt = $expireTime;
        try{
            $this->userService->updateUser($registeredUser);
        } catch (InvalidArgumentException $exception){
            dd('err');
        }
        $mailSending =  $this->userInvitationService->emailVerificationCode($code, $user[0]->email, $user[0]->firstName);
        if ($mailSending){
            return redirect("user-invitations")->with('status', 'A new verification code has been sent to your mail.')->withInput();
        }

    }

    /**
     * @param array $data
     * @return User
     */
    public function mapDataToUserEntity(array $data) : User
    {
        $user = new User();

        if (isset($data["first-name"])) {
            $user->firstName = $data["first-name"];
        }
        if (isset($data["family-name"])) {
            $user->familyName = $data["family-name"];
        }
        if (isset($data["email"])) {
            $user->email = $data["email"];
        }
        if (isset($data["phone"])) {
            $user->phone = $data["phone"];
        }
        if (isset($data["password"])) {
            $user->setPassword($data["password"]);
        }
        if (isset($data["role-id"])) {
            $role = new Role();
            $role->id = $data["role-id"];
            $user->role = $role;
        }
        if (isset($data["permissions"]) && !empty($data["permissions"]))
        {
            foreach ($data['permissions'] as $per) {
                $permission = new Permission();
                $permission->id = $per;
                $user->permissions[] = $permission;
            }
        }
        if (isset($data["customer-id"])) {
            $customer = new Customer();
            $customer->id = $data["customer-id"];
            $user->relatedCustomer = $customer;
        }
        if (isset($data["is-confirmed"])) {
            $user->isConfirmed = $data["is-confirmed"];
        } else {
            $user->isConfirmed = 0;
        }

        return $user;
    }
}

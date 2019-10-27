<?php

namespace App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Abstracts\AbstractController;
use App\Utility\UploadPaths;
use Dev\Application\Exceptions\AuthenticationFailureException;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Domain\Entity\Role;
use Dev\Domain\Entity\User;
use Dev\Domain\Entity\UserNewMail;
use Dev\Domain\Service\PlatformService\PlatformService;
use Dev\Domain\Service\RoleService\RoleService;
use Dev\Domain\Service\UserService\UserNewMailService;
use Dev\Domain\Service\UserService\UserService;
use Dev\Domain\Utility\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Dev\Domain\Service\UserService\PushNotificationTokenService;
use Dev\Domain\Entity\PushNotificationToken;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Dev\Infrastructure\Models\UserModels\UserModel;

/**
 * UserController Class responsible for handling api request and response related to users
 * @package App\Http\Controllers\Api\UserController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class UserController extends AbstractController
{
    /**
     * @var UserService $userService instance from user service
     */
    private $userService;

    /**
     * @var RoleService $roleService instance from role service
     */
    private $roleService;

    /**
     * @var PlatformService $platformService instance from PlatformService
     */
    private $platformService;

    /**
     * @var PushNotificationTokenService $pushService instance from PushNotificationTokenService
     */
    private $pushService;

    /**
     * @var UserNewMailService $userNewMailService instance from UserNewMailService
     */
    private $userNewMailService;

    /**
     * UserController constructor.
     * @param Request $request
     * @param UserService $userService
     * @param RoleService $roleService
     * @param PlatformService $platformService
     * @param PushNotificationTokenService $pushService
     * @param UserNewMailService $userNewMailService
     */
    public function __construct(
        Request $request,
        UserService $userService,
        RoleService $roleService,
        PlatformService $platformService,
        PushNotificationTokenService $pushService,
        UserNewMailService $userNewMailService
    ) {
        parent::__construct($request);
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->platformService = $platformService;
        $this->pushService = $pushService;
        $this->userNewMailService = $userNewMailService;
    }

    /**
     * User register action
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     */
    public function registerAction(Request $request)
    {
        $requestPlatformCode = $request->header("Platform");
        $passwordPattern = "/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/";
        $data = $request->all();
        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "first_name" => "required",
            "password" => "required|max:20|min:8"
        ]);
        $errors = [];
        $validationErrors = $validator->errors();
        if ($validationErrors->has("email")) {
            $errors["errors"]["email"] = "Must define user email";
        }
        if ($validationErrors->has("first_name")) {
            $errors["errors"]["first_name"] = "Must define user first name";
        }
        if ($validationErrors->has("password")) {
            $errors["errors"]["password"] =
                "Must define user password with 8 chars minimum and 20 chars maximum";
        }
        $user = $this->mapDataToUserEntity($data);
        if (!preg_match($passwordPattern, $user->getPlainPassword())) {
            $errors["errors"]["password"] =
                "Must define user password that contains alphanumeric";
        }
        if ($errors) {
            return response($errors, 422);
        }
        $userCriteria = [
            "email" => $user->email
        ];
        $foundUser = $this->userService->getUserWithCriteria($userCriteria);
        if ($foundUser) {
            $errors["errors"]["email"] = "This email already exist";
        }
        if ($errors) {
            return response($errors, 422);
        }
        $platform = $this->platformService->getPlatformWithCode($requestPlatformCode);
        $roleCriteria = [
            "platform" => $platform->id
        ];
        $roles = $this->roleService->getRoleWithCriteria($roleCriteria);
        if (!$roles) {

        }
        $role = $roles[0];
        $userRole = new Role();
        $userRole->id = $role->id;
        $user->role = $userRole;
        $createdUser = $this->userService->addNewUser($user);

        if ($createdUser){
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

            }
            $userName = $createdUser->firstName;
            $email = $this->userService->emailVerificationCode($code, $data['email'], $userName);
            if ($email){
                return response()->json("", 204);
            }else{
                return response()->json("", 478);
            }
        }
    }

    /**
     * checking the email verification code
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws NotFoundException
     */
    public function mailCodeConfirmationAction(Request $request)
    {
        $data = $request->all();
        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }
        $errors = [];
        if (!isset($data['email'])) {
            $errors["errors"]["email"] = "Must send user email";
        }
        if (!isset($data['confirmation_code'])) {
            $errors["errors"]["confirmation_code"] = "Must send email verification code";
        }
        if ($errors) {
            return response($errors, 422);
        }
        $userCriteria = [
            "email" => $data['email']
        ];
        $result = $this->userService->getUserWithCriteria($userCriteria);
        if (!$result) {
            $errors["errors"]["email"] = "No user with that e-mail address.";
            return response($errors, 422);
        }
        $foundUser = $result[0];
        $userId= $foundUser->id;
        $sentCode =  $data['confirmation_code'];
        $criteria = [
            "user-id" => $userId,
            "confirmation_code" => $sentCode
        ];
        $currentUserRecord = $this->userService->getUserWithCriteria($criteria);
        if (empty($currentUserRecord)) {
            $errors["errors"]["confirmation_code"] = "Invalid email confirmation code.";
            return response($errors, 422);
        }
        $userRecord = $currentUserRecord[0];
        $currentTime = date("H:i:s");
        if ($currentTime > $userRecord->confirmationCodeExpiredAt) {
            $errors["errors"]["message"] = "This verification code is expired.";
            return response($errors, 404);
        }
        $user = new User();
        $user->id = $foundUser->id;
        $user->isConfirmed = 1;

        try{
           $updatedUser =  $this->userService->updateUser($user);
        } catch (InvalidArgumentException $exception){

        }

        $token = auth("api")->tokenById($foundUser->id);
        $userData = $this->userService->getUserWithId($foundUser->id);
        $userData->accessToken = $token;
        $userData->accessTokenExpiredAt = auth("api")->factory()->getTTL() * 60;
        return response()->json($userData->getApiResponseObject(), 200);
    }

    /**
     * resend mail confirmation code
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function resendMailCodeConfirmationAction(Request $request)
    {
        $data = $request->all();
        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }
        $errors = [];
        if (!isset($data['email'])) {
            $errors["errors"]["email"] = "Must send user email";
        }
        if ($errors) {
            return response($errors, 422);
        }
        $userCriteria = [
            "email" => $data['email']
        ];
        $userWithCriteria = $this->userService->getUserWithCriteria($userCriteria);
        if (!$userWithCriteria) {
            $errors["errors"]["email"] = "No user with that e-mail address.";
            return response($errors, 422);
        }
            $CodeGenerator = new CodeGenerator($userWithCriteria[0]->id);
            $code = $CodeGenerator->generateCode();
            $registeredUser = new User();
            $registeredUser->id = $userWithCriteria[0]->id;
            $registeredUser->confirmationCode = $code;
            $expireTime = date("H:i:s", strtotime("+1 hours"));
            $registeredUser->confirmationCodeExpiredAt = $expireTime;
            try{
                $this->userService->updateUser($registeredUser);
            } catch (InvalidArgumentException $exception){

            }
            $userName = $userWithCriteria[0]->firstName;
            $mailSending =  $this->userService->emailVerificationCode($code, $data['email'], $userName);
            if ($mailSending){
                return response()->json("", 204);
            }else{
                return response()->json("", 478);
            }
    }

    /**
     * User login action
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws NotFoundException
     */
    public function loginAction(Request $request)
    {
        $data = $request->all();
        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }
        $loggedInUser = $this->mapDataToUserEntity($data);
        $errors = [];
        if (!$loggedInUser->email) {
            $errors["errors"]["email"] = "Must send user email";
        }
        if (!$loggedInUser->getPassword()) {
            $errors["errors"]["password"] = "Must send user password";
        }
        if ($errors) {
            return response($errors, 422);
        }
        try {
            $userToken = $this->userService->authenticateApiUser($loggedInUser);
        } catch (InvalidArgumentException $invalidArgumentException) {
            $errors["errors"]["message"] = "Must define user name and password";
            return response($errors, 404);
        } catch (AuthenticationFailureException $authenticationFailureException) {
            $errors["errors"]["message"] = "This user is not authorized";
            return response($errors, 401);
        }
        if ($userToken)
        {
            $criteria = ["email" => $data['email']];
            $user = $this->userService->getUserWithCriteria($criteria);

            if ($user[0]->isConfirmed == 1){
                $user = $this->userService->getUserWithId(auth("api")->user()->id);
                $user->accessToken = $userToken;
                $user->accessTokenExpiredAt = auth("api")->factory()->getTTL() * 60;
                return response()->json($user->getApiResponseObject(), 200);
            }
            else {
                return response('', 477);
            }
        }
    }

    /**
     * User login action
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     */
    public function logoutAction(Request $request)
    {
        $token = $request->bearerToken();
        $token = JWTAuth::getToken();
        if (empty($token)) {
            return response()->json(['error' => 'Empty token'], 422);
        } else {
            try {
                JWTAuth::invalidate($token);
                return response()->json([
                    'success' => true,
                    'message' => 'User logged out successfully'
                ]);
            } catch (JWTException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, the user cannot be logged out'
                ], 500);
            }
        }
    }

    /**
     * Getting user profile data
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function userProfile(Request $request, int $userId)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->userService->setRequestObject($requestedObject);
        }
        try {
            $user = $this->userService->getUserWithId($userId);
        } catch (NotFoundException $notFoundException) {
            $errors["errors"]["message"] = "This user is not found";
            return response($errors, 404);
        }
        $response["data"][] = $user;
        return response()->json($response, 200);
    }

    /**
     * Update user profile data
     * @param Request $request
     * @param int $id user id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws InvalidArgumentException
     */
    public function editProfileAction(Request $request, int $id)
    {
        try {
            $user = $this->userService->getUserWithId($id);
        } catch (NotFoundException $notFoundException) {
            $errors["errors"]["message"] = "This user is not found";
            return response($errors, 404);
        }
        if ((auth("api")->user()->id) != $id) {
            $errors["errors"]["message"] = "This user has no permission to update this profile";
            return response($errors, 403);
        }
        $data = $request->all();
        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }
        $validator = Validator::make($request->all(), [
            "email" => "email"
        ]);
        $errors = [];
        $validationErrors = $validator->errors();
        if ($validationErrors->has("email")) {
            $errors["errors"]["email"] = "Email is not valid";
        }
        $user = $this->getEditDataUserEntity($data);
        $user->id = $id;
        $updatedUser = $this->userService->updateUser($user);
        if ($updatedUser instanceof User) {
            return response('', 204);
        }
        $response["errors"]["message"] = "User data has not been updated";
        return response()->json($response, 500);
    }

    /**
     * Update user profile photo
     * @param Request $request
     * @param int $id user id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws InvalidArgumentException
     */
    public function editProfilePhotoAction(Request $request, int $id)
    {
        try {
            $user = $this->userService->getUserWithId($id);
        } catch (NotFoundException $notFoundException) {
            $errors["errors"]["message"] = "This user is not found";
            return response($errors, 404);
        }
        $oldProfilePhotoPath = $user->photo;
        if ((auth("api")->user()->id) != $id) {
            $errors["errors"]["message"] = "This user has no permission to update this profile";
            return response($errors, 403);
        }
        $data = $request->all();
        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }
        $validator = Validator::make($request->all(), [
            "photo" => "image"
        ]);
        $errors = [];
        $validationErrors = $validator->errors();
        if ($validationErrors->has("photo")) {
            $errors["errors"]["email"] = "Profile photo is not valid";
        }
        $photoPath = $request->file("photo")->store(UploadPaths::USER_PROFILE_IMAGE_PATH . "/{$id}");
        $user = new User();
        $user->id = $id;
        $user->photo = $photoPath;
        $updatedUser = $this->userService->updateUser($user);
        if ($updatedUser instanceof User) {
            Storage::delete($oldProfilePhotoPath);
            return response('', 204);
        }
        $response["errors"]["message"] = "User profile photo has not been updated";
        return response()->json($response, 500);
    }


    /**
     * change user email request action
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function requestUserEmailChangeAction(Request $request)
    {
        $user = auth("api")->user();
        $errors = [];
        $data = $request->all();

        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }

        if (!isset($data['new_email'])) {
            $errors["errors"]["new_email"] = "Must send user new mail";
        }
        if ($errors) {
            return response($errors, 400);
        }

        $userCriteria = [
            "email" => $data['new_email']
        ];
        $foundUser = $this->userService->getUserWithCriteria($userCriteria);
        if ($foundUser) {
            $errors["errors"]["new_email"] = "This email already exist";
        }

        $emailCriteria = [
            "new-email" => $data['new_email']
        ];
        $foundEmail = $this->userNewMailService->getUserMailWithCriteria($emailCriteria);
        if ($foundEmail) {
            $errors["errors"]["new_email"] = "This email already exist";
        }
        if ($errors) {
            return response($errors, 422);
        }

        $userIdCriteria = [
            "id" => $user->id
        ];

        $userFirstName = $this->userService->getUserWithCriteria($userIdCriteria);

        $data['user_id'] = $user->id;
        $newUserMail = $this->mapDataToUserNewMailEntity($data);

        $criteria = ["user-id" => $user->id];
        $previousRequest = $this->userNewMailService->getUserMailWithCriteria($criteria);
        if ($previousRequest){
            $CodeGenerator = new CodeGenerator($user->id);
            $code = $CodeGenerator->generateCode();
            $userMailUpdate = new UserNewMail();
            $userMailUpdate->id = $previousRequest[0]->id;
            $userMailUpdate->email = $data['new_email'];
            $userMailUpdate->confirmationCode = $code;
            $expireTime = date("H:i:s", strtotime("+1 hours"));
            $userMailUpdate->confirmationCodeExpiredAt = $expireTime;
            try{
                $this->userNewMailService->updateUserMail($userMailUpdate);
            } catch (InvalidArgumentException $exception){

            }

            $userName = $userFirstName[0]->firstName;
            $mailSending = $this->userService->emailVerificationCode($code, $data['new_email'], $userName);
            if ($mailSending){
                return response()->json("", 204);
            }else{
                return response()->json("", 478);
            }
        } else {

            try {
                $createdUserMail = $this->userNewMailService->addNewUserMail($newUserMail);
            } catch (InvalidArgumentException $exception){

            }
            if ($createdUserMail){
                $CodeGenerator = new CodeGenerator($createdUserMail->id);
                $code = $CodeGenerator->generateCode();
                $userMailUpdate = new UserNewMail();
                $userMailUpdate->id = $createdUserMail->id;
                $userMailUpdate->confirmationCode = $code;
                $expireTime = date("H:i:s", strtotime("+1 hours"));
                $userMailUpdate->confirmationCodeExpiredAt = $expireTime;
                try{
                    $this->userNewMailService->updateUserMail($userMailUpdate);
                } catch (InvalidArgumentException $exception){

                }
                $userName = $userFirstName[0]->firstName;
                $mailSending = $this->userService->emailVerificationCode($code, $data['new_email'], $userName);
                if ($mailSending){
                    return response()->json("", 204);
                }else{
                    return response()->json("", 478);
                }
            }
        }
    }

    /**
     * resend new mail confirmation code
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function resendNewMailCodeConfirmationAction(Request $request)
    {
        $user = auth("api")->user();
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->userService->setRequestObject($requestedObject);
        }
        $userCriteria = [
            "user-id" => $user->id
        ];

        $requestWithCriteria = $this->userNewMailService->getUserMailWithCriteria($userCriteria);
        if (!$requestWithCriteria) {
            $errors["errors"]["email"] = "This user has no email change requests.";
            return response($errors, 422);
        }

        $CodeGenerator = new CodeGenerator($user->id);
        $code = $CodeGenerator->generateCode();
        $userMailUpdate = new UserNewMail();
        $userMailUpdate->id = $requestWithCriteria[0]->id;
        $userMailUpdate->confirmationCode = $code;
        $expireTime = date("H:i:s", strtotime("+1 hours"));
        $userMailUpdate->confirmationCodeExpiredAt = $expireTime;
        try{
            $this->userNewMailService->updateUserMail($userMailUpdate);
        } catch (InvalidArgumentException $exception){

        }
        $userMail = $requestWithCriteria[0]->email;
        $userName = $requestWithCriteria[0]->firstName;
        $mailSending = $this->userService->emailVerificationCode($code, $userMail, $userName);
        if ($mailSending){
            return response()->json("", 204);
        }else{
            return response()->json("", 478);
        }
    }

    /**
     * checking the email verification code
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function newMailCodeConfirmationAction(Request $request)
    {
        $userEntity = auth("api")->user();
        $data = $request->all();
        if (!$data) {
            $errorMessage = [
                "errors" => [
                    "message" => "No data received"
                ]
            ];
            return response($errorMessage, 422);
        }
        $errors = [];
        if (!isset($data['confirmation_code'])) {
            $errors["errors"]["confirmation_code"] = "Must send email verification code";
        }
        if ($errors) {
            return response($errors, 422);
        }
        $userCriteria = [
            "user-id" => $userEntity->id,
            "confirmation-code" => $data['confirmation_code']
        ];
        $result = $this->userNewMailService->getUserMailWithCriteria($userCriteria);
        if (!$result) {
            $errors["errors"]["email"] = "No change email request with that e-mail address.";
            return response($errors, 422);
        }
        $foundChangeMailRequest = $result[0];
        $userId= $foundChangeMailRequest->user->id;
        $sentCode =  $data['confirmation_code'];
        $criteria = [
            "user-id" => $userId,
            "confirmation_code" => $sentCode
        ];
        $emailChangeRequest = $this->userNewMailService->getUserMailWithCriteria($criteria);
        if (empty($emailChangeRequest)) {
            $errors["errors"]["confirmation_code"] = "Invalid email confirmation code.";
            return response($errors, 422);
        }
        $requestRecord = $emailChangeRequest[0];
        $currentTime = date("H:i:s");
        if ($currentTime > $requestRecord->confirmationCodeExpiredAt) {
            $errors["errors"]["message"] = "This verification code is expired.";
            return response($errors, 404);
        }
        $userMailEntity = new UserNewMail();
        $userMailEntity->id = $requestRecord->id;
        $userMailEntity->isConfirmed = 1;
        try{
            $newMailConfirmation =  $this->userNewMailService->updateUserMail($userMailEntity);
        } catch (InvalidArgumentException $exception){

        }
        if ($newMailConfirmation){
            $userEntity = new User();
            $userEntity->id = $userId;
            $userEntity->email = $requestRecord->email;
            $userEntity->confirmationCode = '';
            $userEntity->confirmationCodeExpiredAt = 0;
            try{
                $this->userService->updateUser($userEntity);
            } catch (InvalidArgumentException $exception){

            }
            $responseData["data"]["message"] = "User email has been changed";
            return response()->json($responseData, 200);
        }
    }

    /**
     * Map data from request to user entity
     * @param array $data request data
     * @return User
     */
    private function mapDataToUserEntity(array $data) : User
    {
        $user = new User();
        if (isset($data["first_name"]) && !empty(trim($data["first_name"]))) {
            $user->firstName = trim($data["first_name"]);
        }
        if (isset($data["family_name"]) && !empty(trim($data["family_name"]))) {
            $user->familyName = trim($data["family_name"]);
        }
        if (isset($data["email"]) && !empty($data["email"])) {
            $user->email = $data["email"];
        }
        if (isset($data["password"]) && !empty($data["password"])) {
            $user->setPassword($data["password"]);
        }
        if (isset($data["is_confirmed"])) {
            $user->isConfirmed = $data["is_confirmed"];
        } else {
            $user->isConfirmed = 0;
        }
        return $user;
    }

    /**
     * Map data from request to user new mail entity
     * @param array $data request data
     * @return UserNewMail
     */
    private function mapDataToUserNewMailEntity(array $data) : UserNewMail
    {
        $userNewMail = new UserNewMail();
        if (isset($data["new_email"]) && !empty($data["new_email"])) {
            $userNewMail->email = $data["new_email"];
        }
        if (isset($data["user_id"])) {
            $user = new User();
            $user->id = $data["user_id"];
            $userNewMail->user = $user;
        }
        if (isset($data["is_confirmed"])) {
            $userNewMail->isConfirmed = $data["is_confirmed"];
        } else {
            $userNewMail->isConfirmed = 0;
        }
        return $userNewMail;
    }

    /**
     * Map data from request to user entity for updating user data purpose only
     * @param array $data request data
     * @return User
     */
    private function getEditDataUserEntity(array $data) : User
    {
        $user = new User();
        if (isset($data["first_name"]) && !empty(trim($data["first_name"]))) {
            $user->firstName = trim($data["first_name"]);
        }
        if (isset($data["last_name"]) && !empty(trim($data["last_name"]))) {
            $user->familyName = trim($data["last_name"]);
        }
        if (isset($data["phone"]) && !empty($data["phone"])) {
            $user->phone = $data["phone"];
        }
        return $user;
    }

    /**
     * Disable Or Enable user device push notification
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function pushNotificationAction(Request $request)
    {

        // {
        //     "fcmToken" : "APA91bFoi3lMMre9G3XzR1LrF4ZT82_15MsMdEICogXSLB8-MrdkRuRQFwNI5u8Dh0cI90ABD3BOKnxkEla8cGdisbDHl5cVIkZah5QUhSAxzx4Roa7b4xy9tvx9iNSYw-eXBYYd8k1XKf8Q_Qq1X9-x-U-Y79vdPq",
        //     "deviceId" : "ce777617da7f548fe7a9ab6febb56cf39fba6d382000c0395666288d961ee566",
        //     "devicePlatform" : "android-7",
        //     "ip" : "152.116.280.145",
        //     "status" : "1"
        // }
        // die("got it");
        //$data     = $request->all();
        $errors  = ['errors' => []];
        $rawData = json_decode($request->getContent(), true);
        if (!isset($rawData['fcmToken'])) {
            $errors["errors"][] = "fcmToken is required";
        }
        if (!isset($rawData['status'])) {
            $errors["errors"][] = "status is required (0|1)";
        }
        if ($errors["errors"]) {
            return response($errors, 422);
        } else {
            $pushEntity           = New PushNotificationToken();
            $pushEntity->fcmToken = $rawData['fcmToken'];
            $pushEntity->status   = $rawData["status"];
            if (isset($rawData["deviceId"])) {
                $pushEntity->deviceId       = $rawData["deviceId"];
            }
            if (isset($rawData["devicePlatform"])) {
                $pushEntity->devicePlatform       = $rawData["devicePlatform"];
            }
            $pushEntity->ip             = $request->server('HTTP_USER_AGENT');
            $pushEntity->agent          = $request->header('User-Agent');
            try{
                $saved = $this->pushService->savePushNotificatioinToken($pushEntity);
                return response(['data' => $saved], 200);
            } catch (InvalidArgumentException $exception){
                $errors["errors"]["message"] = "Server Error";
                return response($errors, 500);
            }
        }
        // $token1 = $request->bearerToken();
        // $token2 = JWTAuth::getToken();

        // //echo auth("api")->user()->id;
        // echo "\n !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! \n";
        // print_r($token1);
        // echo "\n $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$ \n";
        // print_r($token2);

        // //$validatedToken = JWTAuth::validate($token1);
        // $user           = JWTAuth::authenticate($token1);

        // echo "\n &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& \n";
        // print_r($validatedToken);
        // echo "\n @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ \n";
        // print_r($user);
        // exit;

        // $pushNotificationData = [
        //                             "fcm-token"       => $rawData['fcmToken'],
        //                             "device-id"       => $rawData['deviceId'],
        //                             "device-platform" => $rawData["devicePlatform"],
        //                             "ip"              => $request->server('HTTP_USER_AGENT'),
        //                             "agent"           => $request->header('User-Agent'),
        //                             "status"          => $rawData["status"],
        //                         ];
        //$pushModel = PushNotificationTokenModel::firstOrNew($pushNotificationData);
        // $pushModel  = New PushNotificationTokenModel($pushNotificationData);
        // $oldPushToken = PushNotificationTokenModel::where('fcm-token',$rawData['fcmToken'])->first();
        // if ($oldPushToken) {
        //       //$pushNotificationData['id'] = $oldPushToken->id;
        //       $pushModel->id = $oldPushToken->id;
        // }

        // print_r($oldPushToken->id);
        // print_r($oldPushToken);
        // exit;
        // $savedPush = $pushModel->save();
        // print_r($rawData);
        // print_r($pushEntity);
        // print_r($pushModel);
        // print_r($savedPush);
        // exit;

        // $pushService = New PushNotificationTokenService($pushEntity);
        // $pushEntity->deviceId = "abcdbcdbcdc";
        // try{
        //     $this->pushService->savePushNotificatioinToken($pushEntity);
        //     return "done";
        // } catch (InvalidArgumentException $exception){
        //     $errors["errors"]["message"] = "Must define user id";
        //     return response($errors, 422);
        // }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteByEmail(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->userService->setRequestObject($requestedObject);
        }
        $data = $request->all();
        if (isset($data['email'])) {
            try {
                $isDeleted = UserModel::where('email',$data['email'])->delete();
                if ($isDeleted) {
                    $success = ['message' => 'User deleted successfully'];
                    return response($success, 200);
                } else {
                    $errors["errors"]["message"] = "User not found";
                    return response($errors, 404);
                }
            } catch (Exception $e) {
                $errors["errors"]["message"] = "Unable to delete user, ".$e->getMessage();
                return response($errors, 400);
            }
        } else {
                $errors["errors"]["message"] = "Required email";
                return response($errors, 422); 
        }
    }
}
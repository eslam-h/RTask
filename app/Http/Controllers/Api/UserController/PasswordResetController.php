<?php

namespace App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Abstracts\AbstractApiController;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Domain\Entity\PasswordReset;
use Dev\Domain\Entity\User;
use Dev\Domain\Service\UserService\PasswordResetService;
use Dev\Domain\Service\UserService\UserService;
use Dev\Domain\Utility\CodeGenerator;
use Illuminate\Http\Request;

/**
 * PasswordResetController Class responsible for handling api request and response related to password resetting
 * @package App\Http\Controllers\Api\UserController
 */
class PasswordResetController extends AbstractApiController
{
    /**
     * @var PasswordResetService $passwordResetService instance from password reset service
     */
    private $passwordResetService;

    /**
     * @var UserService $userService instance from user service
     */
    private $userService;

    /**
     * PasswordResetController constructor.
     * @param UserService $userService instance from user service
     * @param PasswordResetService $passwordResetService instance from password reset service
     * @param Request $request instance from Request
     */
    public function __construct(
        Request $request,
        UserService $userService,
        PasswordResetService $passwordResetService
    ) {
        parent::__construct($request);
        $this->passwordResetService = $passwordResetService;
        $this->userService = $userService;
    }

    /**
     * New password request action
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function resetPasswordRequestAction(Request $request)
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
        $result = $this->userService->getUserWithCriteria($userCriteria);
        if (!$result) {
            $errors["errors"]["email"] = "No user with this e-mail address.";
            return response($errors, 422);
        }
        $foundUser = $result[0];
        $passwordResetRequest = new PasswordReset();
        $passwordResetRequest->userId = $foundUser->id;
        $passwordResetCodeGenerator = new CodeGenerator($foundUser->id);
        $code = $passwordResetCodeGenerator->generateCode();
        $passwordResetRequest->code = $code;
        $expireTime =  date("H:i:s", strtotime("+1 hours"));
        $passwordResetRequest->expiredAt = $expireTime;
        try{
             $this->passwordResetService->addNewPasswordResetRequest($passwordResetRequest);
        } catch (InvalidArgumentException $exception){
            $errors["errors"]["message"] = "Must send user email";
            return response($errors, 404);
        }
        $this->passwordResetService->emailVerificationCode($code, $data['email']);
        $responseData["data"]["message"] = "An email has been sent with password reset code.";
        return response()->json($responseData, 200);
    }

    /**
     * Verify password reset code action
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function verifyCodeAction(Request $request)
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
        if (!isset($data['code'])) {
            $errors["errors"]["code"] = "Must send verification code";
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
        $sentCode =  $data['code'];
        $passwordResetCriteria = [
            "user-id" => $userId,
            "code" => $sentCode
        ];
        $passwordResetRequestRecord = $this->passwordResetService->getPasswordResetRequestWithCriteria($passwordResetCriteria);
        if (empty($passwordResetRequestRecord)) {
            $errors["errors"]["code"] = "Invalid reset password code.";
            return response($errors, 422);
        }
        $userRecord = $passwordResetRequestRecord[0];
        $currentTime = date("H:i:s");
        if ($currentTime > $userRecord->expiredAt) {
           $errors["errors"]["message"] = "This password reset code is expired.";
           return response($errors, 404);
        }
        $responseData["data"]["message"] = "The password reset code has been verified";
        return response()->json($responseData, 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resetPasswordAction(Request $request){
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
        if (!isset($data['code'])) {
            $errors["errors"]["code"] = "Must send verification code";
        }
        if (!isset($data['password'])) {
            $errors["errors"]["password"] = "Must send new password";
        }
        if (!isset($data['password-confirmation'])) {
            $errors["errors"]["password-confirmation"] = "Must send new password confirmation";
        }
        if ($errors) {
            return response($errors, 422);
        }
        $userCriteria = [
            "email" => $data['email']
        ];
        $result = $this->userService->getUserWithCriteria($userCriteria);
        if (!$result){
            $errors["errors"]["email"] = "No user with that e-mail address.";
            return response($errors, 422);
        }
        $sentCode =  $data['code'];
        $foundUser = $result[0];
        $userId= $foundUser->id;
        $passwordResetCriteria = [
            "user-id" => $userId,
            "code" => $sentCode
        ];
        $passwordResetRequestRecord = $this->passwordResetService->getPasswordResetRequestWithCriteria($passwordResetCriteria);
        if (empty($passwordResetRequestRecord)) {
            $errors["errors"]["code"] = "Invalid reset password code.";
            return response($errors, 422);
        }
        $userRecord = $passwordResetRequestRecord[0];
        $currentTime = date("H:i:s");
        if ($currentTime > $userRecord->expiredAt) {
            $errors["errors"]["message"] = "This password reset code is expired.";
            return response($errors, 404);
        }
        $newPassword = $data['password'];
        $passwordConfirmation = $data['password-confirmation'];
        if ($newPassword != $passwordConfirmation) {
            $errors["errors"]["message"] = "The passwords are not matched.";
            return response($errors, 422);
        }
        $user = new User();
        $user->id = $userId;
        $user->setPassword($newPassword);
        try{
            $this->userService->updateUser($user);
        } catch (InvalidArgumentException $exception){
            $errors["errors"]["message"] = "Must define user id";
            return response($errors, 422);
        }
        $responseData["data"]["message"] = "Your new password has been saved";
        return response()->json($responseData, 200);
    }
}

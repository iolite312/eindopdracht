<?php

namespace App\Controllers;

use App\Application\Request;
use App\Helpers\TokenHelper;
use App\Repositories\UserRepository;

class ProfileController extends Controller
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function index()
    {
        $decodedToken = TokenHelper::decode(Request::getAuthToken());
        $user = $this->userRepository->getUserByEmail($decodedToken->claims()->get('user')['email']);
        if (!$user) {
            return [
                'status' => 404,
                'message' => 'User not found',
            ];
        }

        return $user->toArray();
    }

    public function update()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $decodedToken = TokenHelper::decode(Request::getAuthToken());

        try {
            $user = $this->userRepository->getUserByEmail($decodedToken->claims()->get('user')['email']);
        } catch (\Exception) {
            return [
                'status' => 500,
                'message' => 'Something went wrong',
            ];
        }

        if (!$user) {
            return [
                'status' => 404,
                'message' => 'User not found',
            ];
        }

        $user->first_name = $data['first_name'] ?? $user->first_name;
        $user->last_name = $data['last_name'] ?? $user->last_name;
        $user->street = $data['street'] ?? $user->street;
        $user->city = $data['city'] ?? $user->city;
        $user->postalcode = $data['postalcode'] ?? $user->postalcode;
        $user->profile_picture = $data['profile_picture'] ?? $user->profile_picture;
        if ($data['password']) {
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        try {
            $this->userRepository->updateUser($user);
        } catch (\Exception) {
            return [
                'status' => 500,
                'message' => 'Something went wrong',
            ];
        }

        $jwtToken = TokenHelper::generateToken($user);

        return [
            'status' => 200,
            'message' => 'User updated',
            'token' => $jwtToken,
            'user' => $user->toArray(),
        ];
    }
}

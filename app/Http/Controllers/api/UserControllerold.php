<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingDetail;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class UserControllerold extends Controller
{

    public function signup(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phonenumber' => 'required|digits_between:7,10',
        ]);

        try {
            // Create the user
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'role_id' => 3,
                'subscription_id' => 1,
            ]);

            // Generate token using Sanctum
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            // Catch any errors and return a 500 response with the error message
            return response()->json(['error' => 'Failed to register user: ' . $e->getMessage()], 500);
        }
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username_or_email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = filter_var($request->username_or_email, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $request->username_or_email)->first()
            : User::where('username', $request->username_or_email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials.'],
            ])->status(401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function test()
    {
        return response()->json([
            'message' => 'Hello World',
        ]);
    }

    public function checkAuthByToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token not provided',
            ], 400);
        }

        $userToken = PersonalAccessToken::findToken($token);

        if (!$userToken) {
            return response()->json([
                'message' => 'Invalid or expired token',
            ], 401);
        }

        $user = $userToken->tokenable;

        $expiresAt = $userToken->created_at->addDays(7);

        if (Carbon::now()->greaterThan($expiresAt)) {
            return response()->json([
                'message' => 'Token has expired',
            ], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => $user,
        ]);
    }

    public function showDashboard(Request $request)
    {
        $request->userId;

        $user = User::where('id', $request->userId)->first();

        $guests = Guest::where('wedding_id', $user->wedding_id)->get();
        $weddingId = Wedding::where('id', $user->wedding_id)->get('id');

        $attendingCount = $guests->where('attending_status', 'attending')->count();
        $notAttendingCount = $guests->where('attending_status', 'not attending')->count();
        $pendingCount = $guests->where('attending_status', 'pending')->count();

        $totalPeople = $guests->sum('number_of_people');
        $totalKids = $guests->sum('number_of_kids');
        $totalGuests = $totalPeople + $totalKids;
        $totalGuest = $guests->count();

        $attendingPercentage = $totalGuests ? ($attendingCount / $totalGuests) * 100 : 0;
        $pendingPercentage = $totalGuests ? ($pendingCount / $totalGuests) * 100 : 0;
        $notAttendingPercentage = $totalGuests ? ($notAttendingCount / $totalGuests) * 100 : 0;

        $peoplePercentage = $totalGuests ? ($totalPeople / $totalGuests) * 100 : 0;
        $kidsPercentage = $totalGuests ? ($totalKids / $totalGuests) * 100 : 0;

        return response()->json([
            'wedding_id' => $weddingId,
            'attending_count' => $attendingCount,
            'not_attending_count' => $notAttendingCount,
            'pending_count' => $pendingCount,
            'attending_percentage' => $attendingPercentage,
            'pending_percentage' => $pendingPercentage,
            'not_attending_percentage' => $notAttendingPercentage,
            'people_percentage' => $peoplePercentage,
            'kids_percentage' => $kidsPercentage,
            'total_people' => $totalPeople,
            'total_kids' => $totalKids,
            'total_guests' => $totalGuests,
            'total_guests_count' => $totalGuest,
        ]);
    }

    public function allGuestsDashboard(Request $request)
    {
        $request->userId;

        $user = User::where('id', $request->userId)->first();

        $guests = Guest::where('wedding_id', $user->wedding_id)->get();
        return response()->json([
            'guests' => $guests,
        ]);
    }

    public function addGuestDashboard(Request $request)
    {
        $request->validate([
            'guestName' => 'required|string',
            'numberOfPeople' => 'required|integer',
            'numberOfKids' => 'required|integer',
        ]);

        $user = User::find($request->userId);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $weddingId = $user->wedding_id;

        $exists = Guest::where('wedding_id', $weddingId)
            ->where('name', $request->guestName)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'The name is already taken for this wedding.'], 400);
        }

        $wedding = Wedding::find($weddingId);
        if (!$wedding) {
            return response()->json(['error' => 'Wedding not found.'], 404);
        }

        $weddingLink = "/" . $weddingId . "/" . $wedding->groom_name . "And" . $wedding->bride_name . "/" . $request->guestName;

        $guest = Guest::create([
            'wedding_id' => $weddingId,
            'wedding_link' => $weddingLink,
            'name' => $request->guestName,
            'attending_status' => 'pending',
            'number_of_people' => $request->numberOfPeople,
            'number_of_kids' => $request->numberOfKids,
            'message' => null,
        ]);

        if ($guest) {
            return response()->json(['guest_Added' => true]);
        } else {
            return response()->json(['guest_Added' => false]);
        }
    }

    public function editGuest(Request $request)
    {
        $validatedData = $request->validate([
            'guestId' => 'required|integer|exists:guests,id',
            'guestName' => 'required|string|max:255',
            'numberOfPeople' => 'required|integer|min:1',
            'numberOfKids' => 'required|integer|min:0',
        ]);

        try {
            $guest = Guest::findOrFail($validatedData['guestId']);
            $wedding = Wedding::where('id', $guest->wedding_id)->first();
            $guest->update([
                'name' => $validatedData['guestName'],
                'number_of_people' => $validatedData['numberOfPeople'],
                'number_of_kids' => $validatedData['numberOfKids'],
                'wedding_link' => "/" . $wedding->id . "/" . $wedding->groom_name . "And" . $wedding->bride_name . "/" . $validatedData['guestName'],
            ]);

            return response()->json([
                'message' => 'Guest updated successfully',
                'guest' => $guest,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Guest not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the guest',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteGuest(Request $request)
    {
        $validatedData = $request->validate([
            'guestId' => 'required|integer|exists:guests,id',
        ]);

        try {
            $guest = Guest::findOrFail($validatedData['guestId']);

            $guest->delete();

            return response()->json([
                'message' => 'Guest deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete guest',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function editAccount(Request $request)
    {
        $user = User::find($request->userId);

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $validatedData = $request->validate([
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'old_password' => 'nullable|string',
            'password' => 'nullable|string|min:8',
        ]);

        $response = [];
        $updated = false;

        if (!empty($validatedData['username'])) {
            if ($user->username != $validatedData['username']) {
                $user->username = $validatedData['username'];
                $response['username'] = 'Username updated successfully.';
                $response['status'] = true;
                $updated = true;
            }
        }

        if (!empty($validatedData['password'])) {
            if (empty($validatedData['old_password'])) {
                $response['error'] = 'Old password is required to change the password.';
                return response()->json($response, 400);
            }

            if (!Hash::check($validatedData['old_password'], $user->password)) {
                $response['error'] = 'The provided old password does not match our records.';
                return response()->json($response, 400);
            }

            if (Hash::check($validatedData['password'], $user->password)) {
                $response['error'] = 'New password cannot be the same as the old password.';
                return response()->json($response, 400);
            }

            $user->password = Hash::make($validatedData['password']);
            $response['password'] = 'Password updated successfully.';
            $response['status'] = true;
            $updated = true;
        }

        if ($updated) {
            $user->save();
            $response['status'] = true;
            $response['message'] = 'Account settings updated successfully.';
            return response()->json($response, 200);
        }

        $response['message'] = 'No changes were made.';
        return response()->json($response, 200);
    }

    public function getWeddingData(Request $request)
    {
        $user = User::find($request->userId);


        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }


        $weddingId = $user->wedding_id;

        if (!$weddingId) {
            return response()->json(['error' => 'Wedding  ID not found.'], 404);
        }

        $wedding = Wedding::find($weddingId);

        if (!$wedding) {
            return response()->json(['error' => 'Wedding not found.'], 404);
        }

        $weddingDetails = WeddingDetail::where('wedding_id', $user->wedding_id)->firstOrFail();


        if (!$weddingDetails) {
            return response()->json(['error' => 'Wedding details not found.'], 404);
        }

        return response()->json([
            'wedding' => $wedding,
            'wedding_details' => $weddingDetails,
        ]);
    }

    public function saveWeddingData(Request $request)
    {

        $user = User::find($request->userId);


        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $wedding = Wedding::where('id', $user->wedding_id)->firstOrFail();

        if (!$wedding) {
            return response()->json(['error' => 'Wedding not found.'], 404);
        }

        $weddingDetails = WeddingDetail::where('wedding_id', $user->wedding_id)->firstOrFail();

        if (!$weddingDetails) {
            return response()->json(['error' => 'Wedding details not found.'], 404);
        }

        $APIweddingDetails = $request->weddingData;

        $guests = Guest::where("wedding_id", $wedding->id)->get();

        foreach ($guests as $guest) {
            $guest->update([
                'wedding_link' => "/" . $wedding->id . "/" . $APIweddingDetails["groom_name"] . "And" . $APIweddingDetails["bride_name"] . "/" . $guest->name,
            ]);
        }


        $wedding->update([
            'groom_name' => $APIweddingDetails['groom_name'],
            'groom_lastname' => $APIweddingDetails['groom_lastname'],
            'bride_name' => $APIweddingDetails['bride_name'],
            "bride_lastname" => $APIweddingDetails['bride_lastname'],
        ]);

        $weddingDetails->update([
            'wedding_date' => $APIweddingDetails['wedding_date'],
            'ceremony_place' => $APIweddingDetails['ceremony_place'],
            'ceremony_time' => $APIweddingDetails['ceremony_time'],
            'ceremony_city' => $APIweddingDetails['ceremony_city'],
            'ceremony_maps' => $APIweddingDetails['ceremony_maps'],
            'party_place' => $APIweddingDetails['party_place'],
            'party_time' => $APIweddingDetails['party_time'],
            'party_maps' => $APIweddingDetails['party_maps'],
            'party_city' => $APIweddingDetails['party_city'],
            'gift_type' => $APIweddingDetails['gift_type'],
            'gift_details' => $APIweddingDetails['gift_details'],
        ]);

        return response()->json([

            'status' => true,
            'message' => 'Wedding data updated successfully.',
        ]);
    }


    public function getCardWeddingDetails(Request $request)
    {
        $weddingId = $request->wedding_id;
        $guest_name = $request->guest_name;
        $bride_name = $request->bride_name;
        $groom_name = $request->groom_name;

        $wedding = Wedding::where('id', $weddingId)->first();
        if (!$wedding) {
            return response()->json(['validdata' => false, 'message' => 'Invalid wedding ID']);
        }

        if ($wedding->groom_name !== $groom_name || $wedding->bride_name !== $bride_name) {
            return response()->json(['validdata' => false, 'message' => 'Invalid groom or bride name']);
        }

        $guest = Guest::where('wedding_id', $weddingId)->where('name', $guest_name)->first();
        if (!$guest) {
            return response()->json(['validdata' => false, 'message' => 'Invalid guest name']);
        }

        $weddingDetails = WeddingDetail::where('wedding_id', $weddingId)->firstOrFail();

        return response()->json([
            'validdata' => true,
            'wedding_detail' => $weddingDetails,
            'wedding' => $wedding,
            'guest' => $guest,
        ]);
    }

    public function setAttendance(Request $request)
    {
        $guestName = $request->guest_name;
        $attending = $request->attending;
        $message = $request->message;

        $guest = Guest::where('name', $guestName)->firstOrFail();

        $guest->attending_status = $attending;
        $guest->message = $message;

        $guest->save();

        return response()->json([
            'status' => true
        ]);
    }
}

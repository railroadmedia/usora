<?php

namespace Railroad\Usora\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'email|unique:' .
                config('usora.database_connection_name') .
                config('usora.tables.users') .
                ',email',
            'display_name' => 'string|max:255|min:2|unique:' .
                config('usora.database_connection_name') .
                config('usora.tables.users') .
                ',display_name',
            'password' => 'string|min:8|max:128|confirmed',

            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'gender' => 'string|in:male,female,other',
            'country' => 'string',
            'region' => 'string',
            'city' => 'string',
            'birthday' => 'string|date',
            'phone_number' => 'string|integer',
            'biography' => 'string',
            'profile_picture_url' => 'string|url',
            'timezone' => 'string|in:' . implode(',', timezone_identifiers_list()),
            'permission_level' => 'string',

            'notify_on_lesson_comment_reply' => 'nullable|boolean',
            'notify_weekly_update' => 'nullable|boolean',
            'notify_on_forum_post_like' => 'nullable|boolean',
            'notify_on_forum_followed_thread_reply' => 'nullable|boolean',
            'notify_on_forum_post_reply' => 'nullable|boolean',
            'notify_on_lesson_comment_like' => 'nullable|boolean',
            'notifications_summary_frequency_minutes' => 'nullable|integer',

            'drums_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'drums_gear_photo' => 'nullable|url',
            'drums_gear_cymbal_brands' => 'nullable|string',
            'drums_gear_set_brands' => 'nullable|string',
            'drums_gear_hardware_brands' => 'nullable|string',
            'drums_gear_stick_brands' => 'nullable|string',

            'guitar_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'guitar_gear_photo' => 'nullable|url',
            'guitar_gear_guitar_brands' => 'nullable|string',
            'guitar_gear_amp_brands' => 'nullable|string',
            'guitar_gear_pedal_brands' => 'nullable|string',
            'guitar_gear_string_brands' => 'nullable|string',

            'piano_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'piano_gear_photo' => 'nullable|url',
            'piano_gear_piano_brands' => 'nullable|string',
            'piano_gear_keyboard_brands' => 'nullable|string',
        ];
    }

    /**
     * @return mixed
     */
    public function onlyAllowed()
    {
        return $this->only(
            [
                'display_name',
                'password',
                'first_name',
                'last_name',
                'gender',
                'country',
                'region',
                'city',
                'birthday',
                'phone_number',
                'biography',
                'profile_picture_url',
                'timezone',
                'permission_level',
                'notify_on_lesson_comment_reply',
                'notify_weekly_update',
                'notify_on_forum_post_like',
                'notify_on_forum_followed_thread_reply',
                'notify_on_forum_post_reply',
                'notify_on_lesson_comment_like',
                'notifications_summary_frequency_minutes',
                'drums_playing_since_year',
                'drums_gear_photo',
                'drums_gear_cymbal_brands',
                'drums_gear_set_brands',
                'drums_gear_hardware_brands',
                'drums_gear_stick_brands',
                'guitar_playing_since_year',
                'guitar_gear_photo',
                'guitar_gear_guitar_brands',
                'guitar_gear_amp_brands',
                'guitar_gear_pedal_brands',
                'guitar_gear_string_brands',
                'piano_playing_since_year',
                'piano_gear_photo',
                'piano_gear_piano_brands',
                'piano_gear_keyboard_brands',
            ]
        );
    }
}

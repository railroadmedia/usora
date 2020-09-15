<?php

namespace Railroad\Usora\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
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
            'email' => 'required|email|max:255|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',email',
            'display_name' => 'required|string|max:64|min:2|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',display_name',
            'password' => 'required|string|min:8|max:128',

            'first_name' => 'string|max:64',
            'last_name' => 'string|max:64',
            'gender' => 'string|in:male,female,other',
            'country' => 'string|max:84',
            'region' => 'string|max:84',
            'city' => 'string|max:84',
            'birthday' => 'string|date',
            'phone_number' => 'string|max:15',
            'biography' => 'string|max:15000',
            'profile_picture_url' => 'string|url|max:1000',
            'timezone' => 'string|in:' . implode(',', timezone_identifiers_list()),
            'permission_level' => 'string|max:255',

            'notify_on_lesson_comment_reply' => 'nullable|boolean',
            'notify_weekly_update' => 'nullable|boolean',
            'notify_on_forum_post_like' => 'nullable|boolean',
            'notify_on_forum_followed_thread_reply' => 'nullable|boolean',
            'notify_on_forum_post_reply' => 'nullable|boolean',
            'notify_on_lesson_comment_like' => 'nullable|boolean',
            'notifications_summary_frequency_minutes' => 'nullable|integer|max:43200',
            'drumeo_ship_magazine' => 'nullable|boolean',
            'magazine_shipping_address_id' => 'nullable|integer',

            'ios_latest_review_display_date' => 'nullable|date',
            'ios_count_review_display' => 'nullable|integer',
            'google_latest_review_display_date' => 'nullable|date',
            'google_count_review_display' => 'nullable|integer',

            'drums_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'drums_gear_photo' => 'nullable|url|max:1000',
            'drums_gear_cymbal_brands' => 'nullable|string|max:255',
            'drums_gear_set_brands' => 'nullable|string|max:255',
            'drums_gear_hardware_brands' => 'nullable|string|max:255',
            'drums_gear_stick_brands' => 'nullable|string|max:255',

            'guitar_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'guitar_gear_photo' => 'nullable|url|max:1000',
            'guitar_gear_guitar_brands' => 'nullable|string|max:255',
            'guitar_gear_amp_brands' => 'nullable|string|max:255',
            'guitar_gear_pedal_brands' => 'nullable|string|max:255',
            'guitar_gear_string_brands' => 'nullable|string|max:255',

            'piano_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'piano_gear_photo' => 'nullable|url|max:1000',
            'piano_gear_piano_brands' => 'nullable|string|max:255',
            'piano_gear_keyboard_brands' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return mixed
     */
    public function onlyAllowed()
    {
        return $this->only(
            [
                'email',
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
                'drumeo_ship_magazine',
                'magazine_shipping_address_id',
                'ios_latest_review_display_date',
                'ios_count_review_display',
                'google_latest_review_display_date',
                'google_count_review_display',
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

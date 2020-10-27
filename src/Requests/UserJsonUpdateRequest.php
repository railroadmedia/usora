<?php

namespace Railroad\Usora\Requests;

/**
 * Class UserJsonUpdateRequest
 *
 * @package Railroad\Usora\Requests
 *
 * @bodyParam data.type string required  Must be 'user'. Example: user
 * @bodyParam data.attributes.email string Example:test@test.te
 * @bodyParam data.attributes.display_name string  Example:John Snow
 * @bodyParam data.attributes.first_name string  Example:John
 * @bodyParam data.attributes.last_name string Example:Snow

 * @bodyParam data.attributes.gender string  Example:female
 * @bodyParam data.attributes.country string
 * @bodyParam data.attributes.region string
 * @bodyParam data.attributes.city string
 * @bodyParam data.attributes.birthday datetime Example:2019-05-21 21:20:10
 * @bodyParam data.attributes.phone_number string Example:0045124512
 * @bodyParam data.attributes.biography text
 * @bodyParam data.attributes.profile_picture_url string Example:''
 * @bodyParam data.attributes.timezone string
 * @bodyParam data.attributes.permission_level integer
 * @bodyParam data.attributes.notify_on_lesson_comment_reply boolean Example:true
 * @bodyParam data.attributes.notify_weekly_update boolean Example:true
 * @bodyParam data.attributes.notify_on_forum_post_like boolean Example:true
 * @bodyParam data.attributes.notify_on_forum_followed_thread_reply boolean Example:true
 * @bodyParam data.attributes.notify_on_forum_post_reply boolean Example:true
 * @bodyParam data.attributes.notify_on_lesson_comment_like boolean Example:true
 * @bodyParam data.attributes.notifications_summary_frequency_minutes boolean Example:true
 * @bodyParam data.attributes.use_legacy_video_player boolean Example:true
 */
class UserJsonUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data.attributes.email' => 'email|max:255|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',email,' .
                $this->route('id'),
            'data.attributes.display_name' => 'string|max:64|min:2|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',display_name,' .
                $this->route('id'),

            /*
             * password validation rules exist in four locations:
             * 1. account creation, by user: \Railroad\Ecommerce\Requests\OrderFormSubmitRequest::rules
             * 2. password change, by user: \Railroad\Usora\Controllers\PasswordController::update
             * 3. reset forgotten password, by user: \Railroad\Usora\Controllers\ResetPasswordController::reset
             * 4. reset user's password, by staff: \Railroad\Usora\Requests\UserJsonUpdateRequest::rules
             */
            'data.attributes.password' => 'string|min:8|max:128|confirmed',

            'data.attributes.first_name' => 'nullable|string|max:64',
            'data.attributes.last_name' => 'nullable|string|max:64',
            'data.attributes.gender' => 'nullable|string|in:male,female,other',
            'data.attributes.country' => 'nullable|string|max:84',
            'data.attributes.region' => 'nullable|string|max:84',
            'data.attributes.city' => 'nullable|string|max:84',
            'data.attributes.birthday' => 'nullable|string|date',
            'data.attributes.phone_number' => 'nullable|string|max:15',
            'data.attributes.biography' => 'nullable|string|max:15000',
            'data.attributes.profile_picture_url' => 'nullable|string|url|max:1000',
            'data.attributes.timezone' => 'nullable|string|in:' . implode(',', timezone_identifiers_list()),
            'data.attributes.permission_level' => 'nullable|string|max:255',

            'data.attributes.notify_on_lesson_comment_reply' => 'nullable|boolean',
            'data.attributes.notify_weekly_update' => 'nullable|boolean',
            'data.attributes.notify_on_forum_post_like' => 'nullable|boolean',
            'data.attributes.notify_on_forum_followed_thread_reply' => 'nullable|boolean',
            'data.attributes.notify_on_forum_post_reply' => 'nullable|boolean',
            'data.attributes.notify_on_lesson_comment_like' => 'nullable|boolean',
            'data.attributes.notifications_summary_frequency_minutes' => 'nullable|integer|max:43200',
            'data.attributes.use_legacy_video_player' => 'nullable|boolean',
            'data.attributes.drumeo_ship_magazine' => 'nullable|boolean',
            'data.attributes.magazine_shipping_address_id' => 'nullable|integer',

            'data.attributes.ios_latest_review_display_date' => 'nullable|date',
            'data.attributes.ios_count_review_display' => 'nullable|integer',
            'data.attributes.google_latest_review_display_date' => 'nullable|date',
            'data.attributes.google_count_review_display' => 'nullable|integer',

            'data.attributes.drums_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.drums_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.drums_gear_cymbal_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_set_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_hardware_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_stick_brands' => 'nullable|string|max:255',

            'data.attributes.guitar_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.guitar_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.guitar_gear_guitar_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_amp_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_pedal_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_string_brands' => 'nullable|string|max:255',

            'data.attributes.piano_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.piano_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.piano_gear_piano_brands' => 'nullable|string|max:255',
            'data.attributes.piano_gear_keyboard_brands' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return mixed
     */
    public function onlyAllowed()
    {
        return $this->only(
            [
                'data.attributes.display_name',
                'data.attributes.password',
                'data.attributes.first_name',
                'data.attributes.last_name',
                'data.attributes.gender',
                'data.attributes.country',
                'data.attributes.region',
                'data.attributes.city',
                'data.attributes.birthday',
                'data.attributes.phone_number',
                'data.attributes.biography',
                'data.attributes.profile_picture_url',
                'data.attributes.timezone',
                'data.attributes.permission_level',
                'data.attributes.notify_on_lesson_comment_reply',
                'data.attributes.notify_weekly_update',
                'data.attributes.notify_on_forum_post_like',
                'data.attributes.notify_on_forum_followed_thread_reply',
                'data.attributes.notify_on_forum_post_reply',
                'data.attributes.notify_on_lesson_comment_like',
                'data.attributes.notifications_summary_frequency_minutes',
                'data.attributes.use_legacy_video_player',
                'data.attributes.drumeo_ship_magazine',
                'data.attributes.magazine_shipping_address_id',
                'data.attributes.ios_latest_review_display_date',
                'data.attributes.ios_count_review_display',
                'data.attributes.google_latest_review_display_date',
                'data.attributes.google_count_review_display',
                'data.attributes.drums_playing_since_year',
                'data.attributes.drums_gear_photo',
                'data.attributes.drums_gear_cymbal_brands',
                'data.attributes.drums_gear_set_brands',
                'data.attributes.drums_gear_hardware_brands',
                'data.attributes.drums_gear_stick_brands',
                'data.attributes.guitar_playing_since_year',
                'data.attributes.guitar_gear_photo',
                'data.attributes.guitar_gear_guitar_brands',
                'data.attributes.guitar_gear_amp_brands',
                'data.attributes.guitar_gear_pedal_brands',
                'data.attributes.guitar_gear_string_brands',
                'data.attributes.piano_playing_since_year',
                'data.attributes.piano_gear_photo',
                'data.attributes.piano_gear_piano_brands',
                'data.attributes.piano_gear_keyboard_brands',
                'data.attributes.support_note',
            ]
        );
    }
}

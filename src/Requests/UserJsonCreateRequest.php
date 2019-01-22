<?php

namespace Railroad\Usora\Requests;

class UserJsonCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data.attributes.email' => 'required|email|unique:' .
                config('usora.') .
                config('usora.tables.users') .
                ',email',
            'data.attributes.display_name' => 'required|string|max:255|min:2|unique:' .
                config('usora.tables.users') .
                ',display_name',
            'data.attributes.password' => 'required|string|min:8|max:128',

            'data.attributes.legacy_drumeo_wordpress_id' => 'nullable|integer',
            'data.attributes.legacy_drumeo_ipb_id' => 'nullable|integer',

            'data.attributes.notify_on_lesson_comment_reply' => 'nullable|boolean',
            'data.attributes.notify_weekly_update' => 'nullable|boolean',
            'data.attributes.notify_on_forum_post_like' => 'nullable|boolean',
            'data.attributes.notify_on_forum_followed_thread_reply' => 'nullable|boolean',
            'data.attributes.notify_on_lesson_comment_like' => 'nullable|boolean',

            'data.attributes.drums_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.drums_gear_photo' => 'nullable|url',
            'data.attributes.drums_gear_cymbal_brands' => 'nullable|string',
            'data.attributes.drums_gear_set_brands' => 'nullable|string',
            'data.attributes.drums_gear_hardware_brands' => 'nullable|string',
            'data.attributes.drums_gear_stick_brands' => 'nullable|string',

            'data.attributes.guitar_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.guitar_gear_photo' => 'nullable|url',
            'data.attributes.guitar_gear_guitar_brands' => 'nullable|string',
            'data.attributes.guitar_gear_amp_brands' => 'nullable|string',
            'data.attributes.guitar_gear_pedal_brands' => 'nullable|string',
            'data.attributes.guitar_gear_string_brands' => 'nullable|string',

            'data.attributes.piano_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.piano_gear_photo' => 'nullable|url',
            'data.attributes.piano_gear_piano_brands' => 'nullable|string',
            'data.attributes.piano_gear_keyboard_brands' => 'nullable|string',
        ];
    }

    /**
     * @return mixed
     */
    public function onlyAllowed()
    {
        return $this->only(
            [
                'data.attributes.email',
                'data.attributes.display_name',
                'data.attributes.password',
                'data.attributes.legacy_drumeo_wordpress_id',
                'data.attributes.legacy_drumeo_ipb_id',
                'data.attributes.notify_on_lesson_comment_reply',
                'data.attributes.notify_weekly_update',
                'data.attributes.notify_on_forum_post_like',
                'data.attributes.notify_on_forum_followed_thread_reply',
                'data.attributes.notify_on_lesson_comment_like',
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
            ]
        );
    }
}

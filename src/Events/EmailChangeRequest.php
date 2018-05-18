<?php

namespace Railroad\Usora\Events;

use Illuminate\Queue\SerializesModels;

class EmailChangeRequest
{
    use SerializesModels;

    /**
     * The new email.
     *
     * @var string
     */
    public $email;

    /**
     * The confirmation token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new event instance.
     *
     * @param  string $user
     * @return void
     */
    public function __construct($token, $email)
    {
        $this->email = $email;
        $this->token = $token;
    }
}

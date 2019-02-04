<?php

namespace Railroad\Usora\Entities\Traits;

trait UserNotificationSettingsProperties
{
    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $notifyOnLessonCommentReply = true;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $notifyWeeklyUpdate = true;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $notifyOnForumPostLike = true;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $notifyOnForumFollowedThreadReply = true;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $notifyOnForumPostReply = true;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $notifyOnLessonCommentLike = true;

    /**
     * @return bool
     */
    public function getNotifyOnLessonCommentReply()
    {
        return $this->notifyOnLessonCommentReply;
    }

    /**
     * @param bool $notifyOnLessonCommentReply
     */
    public function setNotifyOnLessonCommentReply($notifyOnLessonCommentReply)
    {
        $this->notifyOnLessonCommentReply = $notifyOnLessonCommentReply;
    }

    /**
     * @return bool
     */
    public function getNotifyWeeklyUpdate()
    {
        return $this->notifyWeeklyUpdate;
    }

    /**
     * @param bool $notifyWeeklyUpdate
     */
    public function setNotifyWeeklyUpdate($notifyWeeklyUpdate)
    {
        $this->notifyWeeklyUpdate = $notifyWeeklyUpdate;
    }

    /**
     * @return bool
     */
    public function getNotifyOnForumPostLike()
    {
        return $this->notifyOnForumPostLike;
    }

    /**
     * @param bool $notifyOnForumPostLike
     */
    public function setNotifyOnForumPostLike($notifyOnForumPostLike)
    {
        $this->notifyOnForumPostLike = $notifyOnForumPostLike;
    }

    /**
     * @return bool
     */
    public function getNotifyOnForumFollowedThreadReply()
    {
        return $this->notifyOnForumFollowedThreadReply;
    }

    /**
     * @param bool $notifyOnForumFollowedThreadReply
     */
    public function setNotifyOnForumFollowedThreadReply($notifyOnForumFollowedThreadReply)
    {
        $this->notifyOnForumFollowedThreadReply = $notifyOnForumFollowedThreadReply;
    }

    /**
     * @return bool
     */
    public function isNotifyOnForumPostReply()
    {
        return $this->notifyOnForumPostReply;
    }

    /**
     * @param bool $notifyOnForumPostReply
     */
    public function setNotifyOnForumPostReply($notifyOnForumPostReply)
    {
        $this->notifyOnForumPostReply = $notifyOnForumPostReply;
    }

    /**
     * @return bool
     */
    public function getNotifyOnLessonCommentLike()
    {
        return $this->notifyOnLessonCommentLike;
    }

    /**
     * @param bool $notifyOnLessonCommentLike
     */
    public function setNotifyOnLessonCommentLike($notifyOnLessonCommentLike)
    {
        $this->notifyOnLessonCommentLike = $notifyOnLessonCommentLike;
    }
}
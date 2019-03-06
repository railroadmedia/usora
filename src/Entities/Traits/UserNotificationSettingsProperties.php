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
     * If this is null notifications should be sent instantly.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @var integer|null
     */
    protected $notificationsSummaryFrequencyMinutes = null;

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
    public function getNotifyOnForumPostReply()
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

    /**
     * @return int|null
     */
    public function getNotificationsSummaryFrequencyMinutes()
    {
        return $this->notificationsSummaryFrequencyMinutes;
    }

    /**
     * @param int|null $notificationsSummaryFrequencyMinutes
     */
    public function setNotificationsSummaryFrequencyMinutes($notificationsSummaryFrequencyMinutes)
    {
        $this->notificationsSummaryFrequencyMinutes = $notificationsSummaryFrequencyMinutes;
    }


}
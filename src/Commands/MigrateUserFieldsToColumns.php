<?php

namespace Railroad\Usora\Commands;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Repositories\UserRepository;

class MigrateUserFieldsToColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MigrateUserFieldsToColumns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MigrateUserFieldsToColumns';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(
        DatabaseManager $databaseManager,
        UserRepository $userRepository,
        UsoraEntityManager $entityManager
    ) {
        $this->info('Started migrating fields to columns');

        $fields =
            $databaseManager->connection(config('usora.database_connection_name'))
                ->table(config('usora.tables.user_fields'))
                ->get();

        $this->info('Total: ' . $fields->count());

        foreach ($fields as $fieldIndex => $field) {
            /**
             * @var $user User
             */
            $user = $userRepository->find($field->user_id);

            if (empty($user)) {
                $this->info('Skipped field: ' . var_export($field, true));
                continue;
            }

            switch ($field->key) {
                case 'biography':
                    $user->setBiography($field->value);
                    break;
                case 'birthday':
                    $user->setBirthday(!empty($field->value) ? Carbon::parse($field->value) : null);
                    break;
                case 'country':
                    $user->setCountry($field->value);
                    break;
                case 'full_name':
                    $exploded = explode(' ', $field->value);

                    $user->setFirstName($exploded[0] ?? null);
                    $user->setLastName($exploded[1] ?? null);
                    break;
                case 'gender':
                    $user->setGender(!is_null($field->value) ? strtolower($field->value) : null);
                    break;
                case 'notify_on_comment_reply_setting':
                    $user->setNotifyOnLessonCommentReply($field->value);
                    break;
                case 'notify_on_forums_followed_thread_reply_setting':
                    $user->setNotifyOnForumFollowedThreadReply($field->value);
                    break;
                case 'notify_on_forums_reply_setting':
                    $user->setNotifyOnForumFollowedThreadReply($field->value);
                    break;
                case 'notify_on_like_setting':
                    $user->setNotifyOnForumPostLike($field->value);
                    break;
                case 'profile_picture_image_url':
                    $user->setProfilePictureUrl($field->value);
                    break;
                case 'timezone':
                    $user->setTimezone($field->value);
                    break;
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $entityManager->clear();

            if ($fieldIndex % 250 == 0) {
                $this->info($fieldIndex . ' done.');
            }
        }

        $this->info($fieldIndex . ' done.');

        $this->info('Finished migrating fields to columns');

        return true;
    }
}
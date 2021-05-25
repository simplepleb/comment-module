<?php

/**
 * Putting this here to help remind you where this came from.
 *
 * I'll get back to improving this and adding more as time permits
 * if you need some help feel free to drop me a line.
 *
 * * Twenty-Years Experience
 * * PHP, JavaScript, Laravel, MySQL, Java, Python and so many more!
 *
 *
 * @author  Simple-Pleb <plebeian.tribune@protonmail.com>
 * @website https://www.simple-pleb.com
 * @source https://github.com/simplepleb/comment-module
 *
 * @license Free to do as you please
 *
 * @since 1.0
 *
 */

namespace Modules\Comment\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Modules\Comment\Entities\Comment;

class NewCommentAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $comment = $this->comment;
        $user = $notifiable;

        return (new MailMessage())
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', 'https://laravel.com')
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $comment = $this->comment;
        $user = $notifiable;

        return (new SlackMessage())
                ->success()
                ->from('SimplePleb', ':incoming_envelope:')
                ->content('New Comment: '.$comment->name.' | From:'.$comment->user_name)
                ->attachment(function ($attachment) use ($comment) {
                    $attachment->title('Comment '.$comment->id, route('backend.comments.show', $comment->id))
                    ->fields([
                        'Post'    => $comment->post_name,
                        'User'    => $comment->user_name,
                        'Comment' => $comment->name,
                        'Status'  => $comment->status_label_text,
                    ]);
                });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $comment = $this->comment;
        $user = $notifiable;

        $text = 'New Comment | <strong>'.$comment->name.'</strong> on <strong>'.$comment->post_name.'</strong>  by <strong>'.$comment->user_name.'</strong>';

        $url_backend = route('backend.comments.show', $comment->id);

        return [
            'title'         => 'New Comment for review!',
            'module'        => 'Comment',
            'type'          => 'created', // created, published, viewed,
            'icon'          => 'fas fa-comments',
            'text'          => $text,
            'url_backend'   => $url_backend,
            'url_frontend'  => '',
        ];
    }
}

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
 * @license MIT For Premium Clients
 *
 * @since 1.0
 *
 */

namespace Modules\Comment\Entities\Presenters;

use Carbon\Carbon;

trait CommentPresenter
{
    public function getPublishedAtFormattedAttribute()
    {
        $diff = Carbon::now()->diffInHours($this->published_at);

        if ($diff < 24) {
            return $this->published_at->diffForHumans();
        } else {
            return $this->published_at->isoFormat('llll');
        }
    }

    public function getPublishedAtFormattedBengaliAttribute()
    {
        $diff = Carbon::now()->diffInHours($this->published_at);

        if ($diff < 24) {
            return $this->published_at->diffForHumans();
        } else {
            $date_string = $this->published_at->isoFormat('llll');
        }

        $return_string = en2bnDate($date_string);

        return $return_string;
    }

    public function getStatusFormattedAttribute()
    {
        switch ($this->status) {
            case '0':
                return '<span class="badge badge-warning">Pending</span>';
                break;

            case '1':
                return '<span class="badge badge-success">Pubished</span>';
                break;

            case '2':
                return '<span class="badge badge-danger">Rejected</span>';
                break;

            default:
                return '<span class="badge badge-primary">Status:'.$this->status.'</span>';
                break;
        }
    }
}

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

namespace Modules\Comment\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Auth;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;
use Modules\Comment\Http\Requests\Frontend\CommentsRequest;
use Modules\Comment\Notifications\NewCommentAdded;

class CommentsController extends Controller
{
    public function __construct()
    {
        // Page Title
        $this->module_title = 'Comments';

        // module name
        $this->module_name = 'comments';

        // module icon
        $this->module_icon = 'fas fa-comments';

        // module model name, path
        $this->module_model = "Modules\Comment\Entities\Comment";
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'List';

        $$module_name = $module_model::published()->paginate();

        return view(
            "comment::frontend.$module_name.index",
            compact('module_title', 'module_name', "$module_name", 'module_icon', 'module_action', 'module_name_singular')
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = decode_id($id);

        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Show';

        $$module_name_singular = $module_model::whereId($id)->published()->first();

        if (!$$module_name_singular) {
            abort(404);
        }

        return view(
            "comment::frontend.$module_name.show",
            compact('module_title', 'module_name', 'module_icon', 'module_action', 'module_name_singular', "$module_name_singular")
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(CommentsRequest $request)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);

        $module_action = 'Store';

        $data = [
            'name' => $request->name,
            'comment' => $request->comment,
            'user_id' => (isset($request->user_id)) ? decode_id($request->user_id) : null,
            'parent_id' => (isset($request->parent_id)) ? decode_id($request->parent_id) : null,
        ];
        
        if (isset($request->post_id)) {
            $commentable_id = decode_id($request->post_id);

            $commentable_type = "Modules\Article\Entities\Post";

            $row = $commentable_type::findOrFail($commentable_id);

            $$module_name_singular = $row->comments()->create($data);
        }

        if (isset($$module_name_singular)) {
            auth()->user()->notify(new NewCommentAdded($$module_name_singular));
        }

        Flash::success("<i class='fas fa-check'></i> New '".Str::singular($module_title)."' Added")->important();

        Log::info(label_case($module_title.' '.$module_action)." | '".$$module_name_singular->name.'(ID:'.$$module_name_singular->id.") ' by User:".Auth::user()->name.'(ID:'.Auth::user()->id.')');

        return redirect()->back();
    }
}

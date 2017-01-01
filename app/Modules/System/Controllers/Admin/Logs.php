<?php
/**
 * Dasboard - Implements a simple Administration Dashboard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace App\Modules\System\Controllers\Admin;

use Nova\Database\ORM\ModelNotFoundException;
use Nova\Support\Facades\Redirect;
use Nova\Support\Facades\View;

use App\Core\BackendController;

use App\Modules\System\Models\Log;
use App\Modules\System\Models\LogGroup;

use App\Modules\Users\Models\User;


class Logs extends BackendController
{

    public function index($groupId = null)
    {
        $query = Log::with('group');

        if (! is_null($groupId) && is_numeric($groupId)) {
            $query->where('group_id', $groupId);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(50);

        // Retrieve all Log Groups.
        $groups = LogGroup::all();

        // Process the Logs information.
        $logs = array();

        foreach ($items->getItems() as $item) {
            try {
                $user = User::findOrFail($item->user_id);

                //
                $username = $user->username;
            }
            catch (ModelNotFoundException $e) {
                $username = __d('system', 'Unknow User, ID: {0}', $item->user_id);
            }

            array_push($logs, array(
                'id'       => $item->getkey(),
                'date'     => $item->created_at->formatLocalized(__d('system', '%d %b %Y, %H:%M:%S')),
                'username' => $username,
                'group'    => $item->group->name,
                'link'     => isset($item->url) ? parse_url($item->url, PHP_URL_PATH) : '-',
                'message'  => $item->message ?: '-',
            ));
        }

        // Get the pagination links.
        $links = $items->links();

        return $this->getView()
            ->shares('title', __d('logs', 'Logs'))
            ->withGroupId($groupId)
            ->withGroups($groups)
            ->withLogs($logs)
            ->withLinks($links);
    }

    public function clear()
    {
        Log::truncate();

        // Prepare the flash message.
        $status = __d('system', 'The Logs was successfully cleared.');

        return Redirect::to('admin/logs')->withStatus($status);
    }

}

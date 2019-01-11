<?php
/**
 * Created by PhpStorm.
 * User: evileyecc
 * Date: 2019/1/9
 * Time: 5:16
 */

namespace App\Http\Controllers;


use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\BulkWriteException;

class ScanController extends Controller
{

    private function generate_random_letters($length)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }

        return $random;
    }

    public function create(Request $request)
    {
        $result = $request->input('scan');
        if (is_null($result)) {
            return response()->json(['message' => '提交的内容不能为空'])->setStatusCode(422);
        }
        $result = new Collection(explode("\n", $result));
        $tab_count = substr_count($result->first(), "\t");
        $counts = collect();
        if ($tab_count == 3) { //this mean a DScan
            $result->each(function ($item, $index) use ($counts) {
                $temp = explode("\t", $item);
                $counts->put($temp[0], $counts->get($temp[0], 0) + 1);
            });
            $id = $this->generate_random_letters(6);
            try {
                DB::connection('mongodb')->collection('scan')->insert([
                    '_id'         => $id,
                    'result'      => json_encode($counts->toArray()),
                    'active_time' => time(),
                    'type'        => 'dscan'
                ]);
            } catch (BulkWriteException $bulkWriteException) {
                return response()->json(['error' => '服务器出现了一点问题，请尝试重新提交一次'])->setStatusCode(500);
            }

            return response()->json(['scan_result_id' => $id])->setStatusCode(200);
        } else { //local scan
            return response()->json(['message'=>'目前仅支持DScan格式'])->setStatusCode(422);
        }
    }

    public function show(Request $request, $id)
    {
        if ( !$id) {
            return response()->setStatusCode(404);
        }
        $result = DB::connection('mongodb')->collection('scan')->where('_id', '=', $id)->first();
        if ( !$result) {
            return response()->setStatusCode(404);
        }
        $response = $this->generateDScanResult(collect(json_decode($result['result'])));
        DB::connection('mongodb')->collection('scan')->where('_id', '=', $id)->update(['active_time' => time()]);

        return response()->json(['scan_result'=>$response->toArray()]);
    }

    private function generateDScanResult(Collection $counts)
    {
        $ids = $counts->keys()->toArray();
        $types = Type::whereIn('typeID', $ids)->with('group')->get()->keyBy('typeID')->filter(function ($item, $key) {
            return in_array($item->group->categoryID, [
                6,
                22
            ]);
        });
        $response = collect();
        $counts->each(function ($item, $key) use (&$response, $types) {
            $type = $types->get($key);
            if ( !is_null($type)) {
                if (in_array($type->group->categoryID, [6, 22])) {
                    $group = $response->get($type->group->groupID);
                    if (is_null($group)) {
                        $group = [
                            'groupID'    => $type->group->groupID,
                            'categoryID' => $type->group->categoryID,
                            'total'      => 0,
                            'names'      => $type->group->names,
                            'items'      => [],
                        ];
                    }
                    $item_info = [
                        'typeID'  => $key,
                        'amount'  => $item,
                        'groupID' => $type->group->groupID,
                        'names'   => $type->names,
                    ];
                    $group['items'][] = $item_info;
                    $group['total'] = $group['total'] + $item;
                    $response->put($group['groupID'], $group);
                }
            }
        });

        return $response->values();
    }
}